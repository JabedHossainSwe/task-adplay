<?php
require 'vendor/autoload.php';

use MongoDB\Client;
use MongoDB\Driver\ServerApi;

$uri = 'mongodb+srv://xabedhossaiin:Jabedhasan96850@cluster0.o4vvfzv.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0';
$apiVersion = new ServerApi(ServerApi::V1);

// Create MongoDB client
$client = new Client($uri, [], ['serverApi' => $apiVersion]);

try {
    $client->selectDatabase('admin')->command(['ping' => 1]);
    echo "Successfully pinged your MongoDB Atlas deployment!\n";
} catch (Exception $e) {
    printf($e->getMessage());
    exit;
}

$mongoDatabase = $client->selectDatabase('task@adplay');
$mongoCollection = $mongoDatabase->selectCollection('task');

$mysqli = new mysqli("localhost", "root", "", "task@adplay");

if ($mysqli->connect_error) {
    die("Connection to MySQL failed: " . $mysqli->connect_error);
}

$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

if (empty($data['campaigns']) || !is_array($data['campaigns'])) {
    die("Invalid JSON format or missing 'campaigns' array.");
}

$insertResult = $mongoCollection->insertMany($data['campaigns']);


$response = [
    'inserted_count' => count($insertResult->getInsertedIds()),
    'timestamp' => time()
];

$stmt = $mysqli->prepare("INSERT INTO responses (inserted_count, timestamp) VALUES (?, ?)");
$stmt->bind_param("is", $response['inserted_count'], $response['timestamp']);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Data stored successfully in MongoDB and MySQL.";
} else {
    echo "Failed to store data in MySQL.";
}


$stmt->close();
$mysqli->close();
?>
