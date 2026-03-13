<?php 
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../db_connect.php';
require_once '../auth.php';

check_api_key($env);

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_SERVER['PATH_INFO']) ? intval(ltrim($_server['path_info'], '/')) : 0;


if ($method === 'GET') :
	if ($id > 0) :
	$product = $get_product($id);
		if ($product) {
			echo json_encode(['sucess' => true, 'product' => $product]);
		}
		else {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
		}
endif;
	
// add new information (DONE)
// id if we need these last two for apis
elseif ($method === 'POST') :
	// this is data sent to us
	$data = json_decode(file_get_contents('php://input'), true);
	// if nothing was sent, will use form data?
	if (!isset($data)) {
		$data = $_POST;
	}

	$data_keys = ['sku', 'description', 'uom', 'piece', 'length', 'width', 'height', 'weight'];
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
	$new_id = create_product($data);

	if($new_id) {
		http_response_code(200);
		echo json_encode(['success' => true, 'id' => $new_id]);
	}
	else {
		http_repsonse_code(500);
		echo json_encode(['error' => 'Server Error']);
	}
		
	
elseif($method === 'PUT') :
	// this is data sent to us
	$data = json_decode(file_get_contents('php://input'), true);
	// if nothing was sent, will use form data?
	if (!isset($data)) {
		$data = $_POST;
	}
	
	$data_keys = ['sku', 'description', 'uom', 'piece', 'length', 'width', 'height', 'weight'];
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
	
	if(update_product($id, $data)) {
		echo json_encode(['success' => true]);
	}
	else {
		http_repsonse_code(500);
		echo json_encode(['error' => 'Server Error']);
	}	endif;
?>