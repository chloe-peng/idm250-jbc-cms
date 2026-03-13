<?php
require 'db_connect.php';
require './lib/auth.php';
require './lib/orders.php';

require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    $result = delete_order($id); // uses the lib function

    if ($result === false) {
        $_SESSION['error'] = 'Cannot delete this order. Only draft orders can be deleted.';
    } else {
        $_SESSION['success'] = 'Order deleted successfully.';
    }
}

// redirect back to the order records page
header('Location: order-records.php');
exit;
?>