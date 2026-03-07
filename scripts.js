document.addEventListener('DOMContentLoaded', () => {
    
    // ========================================
    // 1. ANIMATED COUNTERS
    // ========================================
    const counters = document.querySelectorAll('.counter');
    const speed = 200;

    const startCounters = () => {
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText.replace(/,/g, '');
                const inc = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc).toLocaleString();
                    setTimeout(updateCount, 15);
                } else {
                    counter.innerText = target.toLocaleString() + "+";
                }
            };
            updateCount();
        });
    };

    const observerOptions = { threshold: 0.5 };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                startCounters();
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all counter elements
    document.querySelectorAll('.counter').forEach(counter => {
        observer.observe(counter);
    });

    // ========================================
    // 2. STEP-BASED FORM LOGIC
    // ========================================
    let currentStep = 1;
    const totalSteps = 5;
    
    // Get elements with null check
    const form = document.getElementById('eligibilityForm');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');
    const resultsArea = document.getElementById('resultsArea');
    const jobCardsContainer = document.getElementById('jobCards');

    if (!form || !nextBtn || !prevBtn || !submitBtn) {
        console.error('Form elements not found. Check HTML IDs.');
        return;
    }

    // Show/Hide form steps
    function updateStep(step) {
        console.log('Updating step to:', step);
        
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(el => {
            el.style.display = 'none';
        });

        // Show current step
        const currentStepEl = document.querySelector(`.form-step[data-step="${step}"]`);
        if (currentStepEl) {
            currentStepEl.style.display = 'block';
        }

        // Update step indicators
        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
            const stepNum = index + 1;
            indicator.classList.remove('active', 'completed');
            
            if (stepNum === step) {
                indicator.classList.add('active');
            } else if (stepNum < step) {
                indicator.classList.add('completed');
                indicator.innerHTML = '<i class="fas fa-check"></i>';
            } else {
                indicator.innerHTML = stepNum;
            }
        });

        // Update step lines
        document.querySelectorAll('.step-line').forEach((line, index) => {
            if (index < step - 1) {
                line.classList.add('active');
            } else {
                line.classList.remove('active');
            }
        });

        // Update button visibility
        prevBtn.style.display = step === 1 ? 'none' : 'inline-block';
        nextBtn.style.display = step === totalSteps ? 'none' : 'inline-block';
        submitBtn.style.display = step === totalSteps ? 'inline-block' : 'none';

        // Scroll to form
        setTimeout(() => {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }

    let isProcessing = false; // Prevent double-clicking

    // Step navigation - Next Button
    nextBtn.addEventListener('click', (e) => {
        e.preventDefault();
        
        if (isProcessing) {
            console.log('Already processing, ignoring click');
            return;
        }
        
        console.log('Next button clicked, currentStep:', currentStep);
        console.log('Validating step:', currentStep);
        
        if (validateStep(currentStep)) {
            console.log('Validation passed, advancing to step:', currentStep + 1);
            isProcessing = true;
            currentStep++;
            updateStep(currentStep);
            setTimeout(() => { isProcessing = false; }, 500); // Reset flag after animation
        } else {
            console.log('Validation failed for step:', currentStep);
        }
    });

    // Step navigation - Previous Button
    prevBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (currentStep > 1) {
            currentStep--;
            updateStep(currentStep);
        }
    });

    // Validate current step
    function validateStep(step) {
        console.log('validateStep called with step:', step);
        switch(step) {
            case 1:
                const qualSelected = document.querySelector('input[name="qualification"]:checked') !== null;
                console.log('Step 1 - Qualification selected:', qualSelected);
                if (!qualSelected) {
                    alert('Please select your qualification to continue');
                }
                return qualSelected;
            case 2:
                const age = document.getElementById('age').value;
                console.log('Step 2 - Age value:', age);
                if (!age) {
                    alert('Please enter your age');
                    return false;
                }
                const ageNum = parseInt(age);
                if (ageNum < 18 || ageNum > 60) {
                    alert('Please enter a valid age (18-60 years)');
                    return false;
                }
                return true;
            case 3:
                const state = document.getElementById('state').value;
                console.log('Step 3 - State value:', state);
                if (!state) {
                    alert('Please select your state');
                    return false;
                }
                return true;
            case 4:
                const catSelected = document.querySelector('input[name="category"]:checked') !== null;
                console.log('Step 4 - Category selected:', catSelected);
                if (!catSelected) {
                    alert('Please select your category');
                }
                return catSelected;
            case 5:
                const jobsSelected = document.querySelectorAll('input[name="jobType"]:checked').length > 0;
                console.log('Step 5 - Jobs selected count:', document.querySelectorAll('input[name="jobType"]:checked').length);
                if (!jobsSelected) {
                    alert('Please select at least one job type');
                }
                return jobsSelected;
            default:
                return true;
        }
    }

    // Initialize form to step 1
    console.log('Initializing form - setting currentStep to 1');
    currentStep = 1;
    updateStep(1);
    console.log('Form initialized on step:', currentStep);

    // ========================================
    // 3. FORM SUBMISSION & RESULTS
    // ========================================
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Collect form data
        const qualification = document.querySelector('input[name="qualification"]:checked')?.value;
        const age = document.getElementById('age').value;
        const state = document.getElementById('state').value;
        const category = document.querySelector('input[name="category"]:checked')?.value;
        const jobTypes = Array.from(document.querySelectorAll('input[name="jobType"]:checked'))
            .map(cb => cb.value);

        const formData = {
            qualification,
            age,
            state,
            category,
            jobTypes
        };

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i> Finding Jobs...';

        try {
            // Fetch from backend
            const response = await fetch('get_jobs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) throw new Error('Network error');
            
            const jobs = await response.json();
            
            // Update profile summary
            updateProfileSummary(formData, jobs);
            
            // Render job cards
            renderJobs(jobs);
            
            // Show results
            resultsArea.classList.remove('hidden');
            resultsArea.scrollIntoView({ behavior: 'smooth' });

        } catch (error) {
            console.error('Error:', error);
            alert('Connection failed. Please ensure PHP server is running.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i> Find Jobs';
        }
    });

    // Update profile summary display
    function updateProfileSummary(data, jobs) {
        document.getElementById('summaryQual').textContent = data.qualification || '-';
        document.getElementById('summaryAge').textContent = data.age || '-';
        document.getElementById('summaryState').textContent = data.state || '-';
        document.getElementById('summaryCategory').textContent = data.category || '-';
        document.getElementById('jobCount').textContent = jobs.length;
    }

    // Render job cards with animations
    function renderJobs(jobs) {
        jobCardsContainer.innerHTML = '';
        
        if (jobs.length === 0) {
            jobCardsContainer.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem; background: white; border-radius: 1rem; border: 2px dashed #e5e7eb;">
                    <i class="fas fa-search" style="font-size: 2rem; color: #9ca3af; margin-bottom: 1rem; display: block;"></i>
                    <p style="color: #6b7280; font-weight: 500; font-size: 1.125rem;">No matching jobs found</p>
                    <small style="color: #9ca3af;">Try selecting different criteria</small>
                </div>
            `;
            return;
        }

        jobs.forEach((job, index) => {
            const card = document.createElement('div');
            card.className = 'job-card';
            card.style.animationDelay = `${index * 0.1}s`;
            
            const deadline = new Date(job.last_date);
            const today = new Date();
            const daysLeft = Math.ceil((deadline - today) / (1000 * 60 * 60 * 24));
            
            const formattedDate = deadline.toLocaleDateString('en-IN', {
                day: 'numeric', month: 'short', year: 'numeric'
            });

            // Generate random match score between 65-100
            const matchScore = Math.floor(Math.random() * 36) + 65;

            card.innerHTML = `
                <div class="dept">${job.department || 'Government'}</div>
                <h4>${job.title}</h4>
                
                <div class="match-score">
                    <span style="font-size: 0.9rem;">Match Score:</span>
                    <div class="match-score-bar">
                        <div class="match-score-bar-fill" style="width: ${matchScore}%"></div>
                    </div>
                    <span style="color: #10b981; min-width: 40px;">${matchScore}%</span>
                </div>

                <div class="job-info">
                    <div>
                        <i class="fas fa-graduation-cap"></i>
                        <span><strong>Qualification:</strong> ${job.qualification || 'Any'}</span>
                    </div>
                    <div>
                        <i class="fas fa-birthday-cake"></i>
                        <span><strong>Age:</strong> ${job.min_age || '18'} - ${job.max_age || '45'} years</span>
                    </div>
                    <div>
                        <i class="fas fa-map-marker-alt"></i>
                        <span><strong>Location:</strong> ${job.state || 'Pan-India'}</span>
                    </div>
                    <div>
                        <i class="fas fa-briefcase"></i>
                        <span><strong>Vacancies:</strong> ${job.vacancies || 'Multiple'}</span>
                    </div>
                    <div>
                        <i class="fas fa-calendar-alt"></i>
                        <span class="last-date"><strong>Deadline:</strong> ${formattedDate} (${daysLeft} days left)</span>
                    </div>
                </div>

                <a href="${job.apply_link || '#'}" target="_blank" class="block w-full mt-4 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg font-semibold text-center hover:shadow-lg transition-all" style="text-decoration: none;">
                    <i class="fas fa-external-link-alt mr-2"></i> Apply Now
                </a>
            `;
            jobCardsContainer.appendChild(card);
        });
    }

    // ========================================
    // 4. FILTER JOBS
    // ========================================
    document.querySelectorAll('.quick-filter').forEach(btn => {
        btn.addEventListener('click', () => {
            // Update active state
            document.querySelectorAll('.quick-filter').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filter = btn.getAttribute('data-filter');
            const cards = document.querySelectorAll('.job-card');

            cards.forEach(card => {
                if (filter === 'all') {
                    card.style.display = 'block';
                } else if (filter === 'high-salary') {
                    // Simple demo: show all (in real app, check salary data)
                    card.style.display = 'block';
                } else if (filter === 'closing-soon') {
                    // Simple demo: show all (in real app, check deadline)
                    card.style.display = 'block';
                }
            });
        });
    });

    // ========================================
    // 5. FLOATING AI COPILOT WITH GEMINI API
    // ========================================
    const aiBtn = document.getElementById('aiCopilotBtn');
    const chatPanel = document.getElementById('chatbotPanel');
    const closeBtn = document.getElementById('closeChatBtn');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');

    // Toggle chat panel
    aiBtn.addEventListener('click', () => {
        if (chatPanel.classList.contains('hidden')) {
            chatPanel.classList.remove('hidden');
            aiBtn.style.display = 'none';
        }
    });

    closeBtn.addEventListener('click', () => {
        chatPanel.classList.add('hidden');
        aiBtn.style.display = 'flex';
    });

    // Handle chat messages with Gemini API via backend
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = chatInput.value.trim();
        
        if (!message) return;

        // Add user message to UI
        const userMsg = document.createElement('div');
        userMsg.className = 'flex justify-end';
        userMsg.innerHTML = `
            <div class="bg-blue-600 text-white px-4 py-2 rounded-lg max-w-xs text-sm rounded-br-none">
                ${message}
            </div>
        `;
        chatMessages.appendChild(userMsg);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Clear input
        chatInput.value = '';

        // Show loading indicator
        const loadingMsg = document.createElement('div');
        loadingMsg.className = 'flex justify-start';
        loadingMsg.innerHTML = `
            <div class="bg-gray-200 text-gray-900 px-4 py-2 rounded-lg max-w-xs text-sm rounded-tl-none flex items-center gap-2">
                <span class="inline-block w-2 h-2 bg-gray-600 rounded-full animate-bounce"></span>
                <span class="inline-block w-2 h-2 bg-gray-600 rounded-full animate-bounce" style="animation-delay: 0.2s;"></span>
                <span class="inline-block w-2 h-2 bg-gray-600 rounded-full animate-bounce" style="animation-delay: 0.4s;"></span>
            </div>
        `;
        chatMessages.appendChild(loadingMsg);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        try {
            // Send message to backend AI API
            const response = await fetch('ai_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: message })
            });

            // Remove loading indicator
            if (chatMessages.contains(loadingMsg)) {
                chatMessages.removeChild(loadingMsg);
            }

            const data = await response.json();

            if (!response.ok || !data.success) {
                // Show error message
                const errorMsg = document.createElement('div');
                errorMsg.className = 'flex justify-start';
                errorMsg.innerHTML = `
                    <div class="bg-red-100 text-red-900 px-4 py-2 rounded-lg max-w-xs text-sm rounded-tl-none">
                        ⚠️ API Error: ${data.error || 'Failed to get response'}
                    </div>
                `;
                chatMessages.appendChild(errorMsg);
                chatMessages.scrollTop = chatMessages.scrollHeight;
                return;
            }

            // Add AI response
            const aiMsg = document.createElement('div');
            aiMsg.className = 'flex justify-start';
            aiMsg.innerHTML = `
                <div class="bg-gray-200 text-gray-900 px-4 py-2 rounded-lg max-w-xs text-sm rounded-tl-none">
                    ${data.response}
                </div>
            `;
            chatMessages.appendChild(aiMsg);
            chatMessages.scrollTop = chatMessages.scrollHeight;

        } catch (error) {
            console.error('Chat error:', error);
            
            // Remove loading indicator
            if (chatMessages.contains(loadingMsg)) {
                chatMessages.removeChild(loadingMsg);
            }

            // Show error message
            const errorMsg = document.createElement('div');
            errorMsg.className = 'flex justify-start';
            errorMsg.innerHTML = `
                <div class="bg-red-100 text-red-900 px-4 py-2 rounded-lg max-w-xs text-sm rounded-tl-none">
                    ⚠️ Error: ${error.message}
                </div>
            `;
            chatMessages.appendChild(errorMsg);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    });

    // ========================================
    // 6. SMOOTH SCROLL ENHANCEMENT
    // ========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && document.querySelector(href)) {
                e.preventDefault();
                document.querySelector(href).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

});