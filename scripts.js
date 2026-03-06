document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Animated Counters (Kept as is - works great!)
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

    if(document.querySelector('.stats')) {
        observer.observe(document.querySelector('.stats'));
    }

    // 2. REAL BACKEND LOGIC (Replacing Mock Logic)
    const form = document.getElementById('eligibilityForm');
    const resultsArea = document.getElementById('resultsArea');
    const jobCardsContainer = document.getElementById('jobCards');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // UI Feedback: Show loading state
        const btn = form.querySelector('button');
        const originalText = btn.innerText;
        btn.innerText = "Searching Database...";
        btn.disabled = true;

        // Collect data from the form
        const selectedJobTypes = Array.from(document.querySelectorAll('input[name="jobType"]:checked'))
                                     .map(cb => cb.value);

        const formData = {
            qualification: document.getElementById('qualification').value,
            age: document.getElementById('age').value,
            state: document.getElementById('state').value,
            jobTypes: selectedJobTypes
        };

        try {
            // Fetch data from PHP Backend
            const response = await fetch('get_jobs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) throw new Error('Network response was not ok');

            const jobs = await response.json();
            
            // Show results
            resultsArea.classList.remove('hidden');
            renderJobs(jobs);
            
            // Smooth scroll to results
            resultsArea.scrollIntoView({ behavior: 'smooth' });

        } catch (error) {
            console.error('Error:', error);
            alert("Connection to server failed. Please ensure your PHP server is running.");
        } finally {
            btn.innerText = originalText;
            btn.disabled = false;
        }
    });

    // Modified to accept 'jobs' parameter from the API
    function renderJobs(jobs) {
        jobCardsContainer.innerHTML = '';
        
        if (jobs.length === 0) {
            jobCardsContainer.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1;">
                    <i class="fas fa-search" style="font-size: 2rem; color: #94a3b8; margin-bottom: 1rem;"></i>
                    <p style="color: #64748b; font-weight: 500;">No matching jobs found for your criteria.</p>
                    <small>Try selecting a different qualification or state.</small>
                </div>
            `;
            return;
        }

        jobs.forEach(job => {
            const card = document.createElement('div');
            card.className = 'job-card';
            
            // Format date for better display
            const formattedDate = new Date(job.last_date).toLocaleDateString('en-IN', {
                day: 'numeric', month: 'short', year: 'numeric'
            });

            card.innerHTML = `
                <div class="dept">${job.department}</div>
                <h4>${job.title}</h4>
                <div class="job-info">
                    <div><i class="fas fa-graduation-cap"></i> ${job.qualification}</div>
                    <div><i class="fas fa-user-clock"></i> Age: ${job.min_age} - ${job.max_age} yrs</div>
                    <div><i class="fas fa-map-marker-alt"></i> ${job.state}</div>
                    <div class="last-date"><i class="fas fa-calendar-alt"></i> Apply by: ${formattedDate}</div>
                </div>
                <a href="${job.apply_link}" target="_blank" class="btn btn-outline btn-block" style="padding: 0.5rem; text-align: center;">View Details</a>
            `;
            jobCardsContainer.appendChild(card);
        });
    }
});