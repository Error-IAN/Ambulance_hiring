<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Methods: POST'); 

// Database connection details
$host = "localhost";
$port = "5432";
$dbname = "testdb";
$user = "debnathsoumyaraj";
$password = "abha4567@";

// Data Source Name
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    error_log("Database connection successful");
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    error_log("Database connection failed: " . $e->getMessage());
    exit();
}

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);
error_log("Received input: " . print_r($input, true));

if (!$input || !isset($input['name']) || !isset($input['email']) || !isset($input['phone']) || !isset($input['address']) || !isset($input['password'])) {
    echo json_encode(['error' => 'Invalid input']);
    error_log("Invalid input");
    exit();
}

$name = $input['name'];
$email = $input['email'];
$phone = $input['phone'];
$address = $input['address'];
$password = password_hash($input['password'], PASSWORD_BCRYPT);
$latitude = isset($input['latitude']) ? $input['latitude'] : null;
$longitude = isset($input['longitude']) ? $input['longitude'] : null;

// Prepare the SQL statement
$sql = "INSERT INTO testdb.\"user\" (name, email, phone_number, location, password, latitude, longitude) VALUES (:name, :email, :phone, :address, :password, :latitude, :longitude)";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':name', $name);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':phone', $phone);
$stmt->bindParam(':address', $address);
$stmt->bindParam(':password', $password);
$stmt->bindParam(':latitude', $latitude);
$stmt->bindParam(':longitude', $longitude);

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
    error_log("User registered successfully");
} else {
    $response['success'] = false;
    $response['error'] = 'Failed to register user';
    error_log("Failed to register user");
}

echo json_encode($response);
?>
