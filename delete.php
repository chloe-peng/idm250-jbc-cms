<?php
require 'db_connect.php';
require './lib/cms.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    delete_product($id);
}

header('Location: sku-management.php');
exit;
?>