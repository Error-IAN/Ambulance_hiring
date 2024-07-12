<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); 

session_start(); // Start the session at the beginning

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];
error_log("Request URI: " . $request_uri);
error_log("Request Method: " . $request_method);

switch ($request_uri) {
    case '/signup':
        require 'signup.php';
        break;
    case '/login':
        require 'login.php';
        break;
    case '/request':
        require 'request.php';
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;
}
?>
