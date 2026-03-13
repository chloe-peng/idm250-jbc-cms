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
    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true);

    $reference_number = $data['reference_number'] ?? '';
    $trailer_number =   $data['trailer_number'] ?? '';
    $expected_arrival = $data['expected_arrival'] ?? '';
    $unit_ids         = $data['unit_ids'] ?? [];

    if (empty($unit_ids)) {
        http_response_code(400);
        echo json_encode(['error' => 'No units selected']);
        exit;
    }

    $data_keys = ['reference_number', 'trailer_number', 'expected_arrival'];
	foreach ($data_keys as $key) {
		if (!isset($data[$key])) {
			http_response_code(400);
			echo json_encode([
				'error' => 'Bad Request',
				'details' => "Missing required field: $key"
			]);
			exit;
		}
	}
	$mpl_id = create_mpl($data, $unit_ids);

	if($mpl_id) {
		http_response_code(200);
		    echo json_encode([
                'success' => true,
                'reference_number' => $reference_number,
                'trailer_number' => $trailer_number,
                'expected_arrival' => $expected_arrival,
                'created_at' => date('Y-m-d H:i:s'),
                'unit_amount' => count($unit_ids),
                'units' => $unit_info
            ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create MPL']);
    }
}

?>