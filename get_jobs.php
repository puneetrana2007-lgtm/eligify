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
    // Create a string for the IN clause (?,?,?)
    $placeholders = implode(',', array_fill(0, count($jobTypes), '?'));
    $sql .= " AND job_type IN ($placeholders)";
}

$stmt = $pdo->prepare($sql);

// Bind basic parameters
$stmt->bindParam(':qual', $qualification);
$stmt->bindParam(':age', $age);
$stmt->bindParam(':state', $state);

// Bind Job Type array parameters if they exist
if (!empty($jobTypes)) {
    $stmt->execute(array_merge([$qualification, $age, $state], $jobTypes));
} else {
    $stmt->execute();
}

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);
?>