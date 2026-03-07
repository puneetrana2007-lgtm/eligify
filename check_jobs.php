<?php
header("Content-Type: application/json");
require 'db.php';

// Test 1: Check if jobs table exists and get structure
$output = [];

try {
    // Check table exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM jobs");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $output['total_jobs'] = $result['count'];
} catch (Exception $e) {
    $output['error_count'] = $e->getMessage();
}

// Test 2: Get first 5 jobs
try {
    $stmt = $pdo->query("SELECT * FROM jobs LIMIT 5");
    $output['sample_jobs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $output['error_sample'] = $e->getMessage();
}

// Test 3: Test with hardcoded values
try {
    $sql = "SELECT * FROM jobs WHERE qualification = 'Bachelor' LIMIT 3";
    $stmt = $pdo->query($sql);
    $output['bachelor_jobs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $output['bachelor_count'] = count($output['bachelor_jobs']);
} catch (Exception $e) {
    $output['error_bachelor'] = $e->getMessage();
}

// Test 4: Show all unique qualifications
try {
    $stmt = $pdo->query("SELECT DISTINCT qualification FROM jobs");
    $output['qualifications'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $output['error_qual'] = $e->getMessage();
}

// Test 5: Show all unique states
try {
    $stmt = $pdo->query("SELECT DISTINCT state FROM jobs");
    $output['states'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $output['error_states'] = $e->getMessage();
}

// Test 6: Show all unique job_types
try {
    $stmt = $pdo->query("SELECT DISTINCT job_type FROM jobs");
    $output['job_types'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $output['error_types'] = $e->getMessage();
}

// Test 7: Test age range query
try {
    $sql = "SELECT COUNT(*) as count FROM jobs WHERE 21 BETWEEN min_age AND max_age";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $output['jobs_for_age_21'] = $result['count'];
} catch (Exception $e) {
    $output['error_age'] = $e->getMessage();
}

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
