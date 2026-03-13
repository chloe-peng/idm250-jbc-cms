<?php

require './lib/mpl.php'; // where send_to_wms lives
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: mpl-records.php");
    exit;
}

$mpl_id = $_POST['mpl_id'] ?? null;

if (!$mpl_id) {
    header("Location: mpl-records.php?error=invalid");
    exit;
}

$result = send_mpl_to_wms($mpl_id);

if ($result['success']) {
    header("Location: mpl-records.php?sent=1");
    exit;
} else {
    header("Location: mpl-records.php?error=failed");
    exit;
}

?>