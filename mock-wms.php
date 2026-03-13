<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

echo json_encode([
    'success' => true,
    'units_count' => count($input['items'] ?? []),
    'reference_number' => $input['reference_number'] ?? null
]);

?>