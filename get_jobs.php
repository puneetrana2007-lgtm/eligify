<?php
header("Content-Type: application/json");
require 'db.php';

// Get JSON data from the frontend request
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "No data provided"]);
    exit;
}

$qualification = $data['qualification'];
$age = (int)$data['age'];
$state = $data['state'];
$jobTypes = $data['jobTypes']; // This is an array

// Build the SQL Query
// Filter by Qualification, Age range, and State (User state OR All India)
$sql = "SELECT * FROM jobs WHERE 
        qualification = :qual AND 
        :age BETWEEN min_age AND max_age AND 
        (state = :state OR state = 'All India')";

// Add Job Type filtering if any are selected
if (!empty($jobTypes)) {
    $placeholders = [];
    foreach (array_keys($jobTypes) as $index) {
        $placeholders[] = ':jobType' . $index;
    }
    $sql .= " AND job_type IN (" . implode(',', $placeholders) . ")";
}

$stmt = $pdo->prepare($sql);

// Prepare parameters array
$params = [
    ':qual' => $qualification,
    ':age' => $age,
    ':state' => $state
];

// Add job type parameters if they exist
if (!empty($jobTypes)) {
    foreach ($jobTypes as $index => $jobType) {
        $params[':jobType' . $index] = $jobType;
    }
}

// Execute with parameters
$stmt->execute($params);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);
?>