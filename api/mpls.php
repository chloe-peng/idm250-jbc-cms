<?php 
ob_start();

define('API_REQUEST', true);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, x-api-key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../db_connect.php';
require_once '../lib/auth.php';
require_once '../lib/mpl.php'; 

ob_end_clean();

check_api_key($env);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input    = file_get_contents('php://input');
    $data     = json_decode($input, true);  

    $action = $data['action']           ?? null;
    $ref    = $data['reference_number'] ?? null; 

    if (!$action || !$ref) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing action or reference_number']);
        exit;
    }

    if ($action === 'confirm') {
        handle_confirm($ref);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Unknown action: $action"]);
        exit;
    }
}

?>