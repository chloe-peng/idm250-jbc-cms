<?php
require 'db_connect.php';
require './lib/auth.php';
require './lib/mpl.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    $result = delete_mpl($id);
    
    if ($result === false) {
        $_SESSION['error'] = 'Cannot delete this MPL.';
    } else {
        $_SESSION['success'] = 'MPL deleted successfully.';
    }
}

header('Location: mpl-records.php');
exit;
?>