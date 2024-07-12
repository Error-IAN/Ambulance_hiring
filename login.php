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

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
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

if (!$input || !isset($input['email']) || !isset($input['password'])) {
    echo json_encode(['error' => 'Invalid input']);
    error_log("Invalid input");
    exit();
}

$email = $input['email'];
$password = $input['password'];

// Prepare the SQL statement
$sql = "SELECT id, email, password FROM testdb.\"user\" WHERE email = :email AND password = :password";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $password);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$response = [];
if ($user) {
    error_log("User found: " . print_r($user, true));
    $response['exists'] = true;
    $response['status'] = 'success';
    $response['message'] = 'Login successful';
    $response['user'] = [
        'id' => $user['id'],
        'email' => $user['email'],
        'password' => $user['password']
    ];
} else {
    $response['exists'] = false;
    $response['status'] = 'error';
    $response['message'] = 'Invalid email or password';
    error_log("User not found");
}

echo json_encode($response);
error_log("Response: " . json_encode($response));
?>
