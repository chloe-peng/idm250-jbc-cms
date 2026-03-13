<!-- Do not use this one -->

<?php
require 'db_connect.php';
require './lib/auth.php';

// require_login();
$warehouse_inventory = get_inventory('internal');
$inventory_amount = $warehouse_inventory['total'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($id) {
        update_product($id, $_POST);
    } else {
        create_product($_POST);
    }
    header('Location: sku-management.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test</title>
</head>
<body>
    <h1>Testing Create MPL</h1>
    <form action="path_to_processing_file.php" method="POST">
    <fieldset>
        <div>
            <label for="reference_number">Reference Number</label>
            <input type="text" name="reference_number" id="reference_number" required>
        </div>
        <section>
            <p>Available Items</p>
            <ul>
               <?php foreach ($warehouse_inventory['inventory'] as $unit): ?>
                    <li><input type="checkbox" name="units[]" value="<?= $unit ?>"> <?= $unit ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <div>
            <button type="submit">Send MPL</button>
        </div>
    </fieldset>
</form>
</body>
</html>