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

if (!$input || !isset($input['reason']) || !isset($input['location']) || !isset($input['email']) || !isset($input['password'])) {
    echo json_encode(['error' => 'Invalid input']);
    error_log("Invalid input");
    exit();
}

$email = $input['email'];
$password = $input['password'];

// Fetch user ID using email and password
$sql = "SELECT id FROM testdb.user WHERE email = :email AND password = :password";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $password);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    error_log("User not found");
    exit();
}

$user_id = $user['id'];
error_log("User ID fetched: " . $user_id);

$reason = $input['reason'];
$location = $input['location'];
$latitude = isset($input['latitude']) ? $input['latitude'] : null;
$longitude = isset($input['longitude']) ? $input['longitude'] : null;

// Insert user request into UserRequest table
$sql = "INSERT INTO testdb.\"UserRequest\" (user_id, reason, location, latitude, longitude) 
        VALUES (:user_id, :reason, :location, :latitude, :longitude) RETURNING id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':reason', $reason);
$stmt->bindParam(':location', $location);
$stmt->bindParam(':latitude', $latitude);
$stmt->bindParam(':longitude', $longitude);
$stmt->execute();
$user_request_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

// Fetch all ambulances
$sql = "SELECT * FROM testdb.\"Ambulance\"";
$ambulances = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Function to calculate distance between two coordinates
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // Radius of the Earth in meters
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    $dLat = $lat2 - $lat1;
    $dLon = $lon2 - $lon1;

    $a = sin($dLat / 2) * sin($dLat / 2) + cos($lat1) * cos($lat2) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}

// Find the nearest available ambulance
$foundAmbulance = false;
foreach ($ambulances as $ambulance) {
    $distance = calculateDistance($latitude, $longitude, $ambulance['latitude'], $ambulance['longitude']);
    if ($distance <= 300) {
        $foundAmbulance = true;
        // Assign the ambulance
        $assignQuery = $pdo->prepare('INSERT INTO testdb."Assigned" (user_request_id, ambulance_id) VALUES (:user_request_id, :ambulance_id)');
        $assignQuery->execute([':user_request_id' => $user_request_id, ':ambulance_id' => $ambulance['id']]);
        break;
    }
}

$response = ['success' => true, 'message' => 'Request submitted successfully!'];
if ($foundAmbulance) {
    $response['ambulanceAssigned'] = true;
    error_log("Ambulance assigned");
} else {
    $response['ambulanceAssigned'] = false;
    error_log("No suitable ambulance found");
}

echo json_encode($response);
error_log("Response: " . json_encode($response));
?>
