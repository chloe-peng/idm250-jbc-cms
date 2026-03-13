<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'db_connect.php';
require './lib/auth.php';
require './lib/orders.php';

require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$order = $id ? get_order($id) : [];

// Only draft orders can be edited
if ($id && $order && $order['status'] !== 'draft') {
    $_SESSION['error'] = 'Only draft orders can be edited.';
    header('Location: order-records.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'order_number'     => $_POST['order_number'],
        'ship_to_company'  => $_POST['ship_to_company'],
        'ship_to_street'   => $_POST['ship_to_street'],
        'ship_to_city'     => $_POST['ship_to_city'],
        'ship_to_state'    => $_POST['ship_to_state'],
        'ship_to_zip'      => $_POST['ship_to_zip']
    ];

    $unit_numbers = isset($_POST['unit_ids']) ? $_POST['unit_ids'] : [];

    if (empty($data['order_number']) || empty($unit_numbers)) {
        $error = "Order number and at least one unit are required.";
    } else {
        if ($id) {
            $result = update_order($id, $data, $unit_numbers);
        } else {
            $result = create_order($data, $unit_numbers);
        }

        if ($result) {
            $_SESSION['success'] = $id ? 'Order updated successfully.' : 'Order created successfully.';
            header('Location: order-records.php');
            exit;
        } else {
            $error = 'Error unable to save the order.';
        }
    }
}

// Fetch available units
$inventory_query = "SELECT i.unit_number, i.ficha, s.sku, s.description
                    FROM inventory i
                    LEFT JOIN cms_products s ON i.ficha = s.ficha
                    WHERE i.location='internal'
                    ORDER BY i.unit_number DESC";
$inventory_result = $connection->query($inventory_query);
$available_units = $inventory_result->fetch_all(MYSQLI_ASSOC);

// Fetch selected units if editing
$selected_unit_numbers = [];
if ($id) {
    $stmt = $connection->prepare("SELECT unit_number FROM order_items WHERE order_id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $selected_unit_numbers[] = $row['unit_number'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Edit' : 'Create'; ?> Order - JBC CMS</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/mpl-form.css">
</head>
<body>
<div class="header-bar">
    <h2>JBC Manufacturing CMS</h2>
    <div class="header-bar-right">
        <h5><?= htmlspecialchars($_SESSION['user_email']); ?></h5>
        <a href="logout.php"><h5>Logout</h5></a>
    </div>
</div><!-- end header-bar -->

<div class="page-wrapper">
    <div class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item">
                <a style="text-decoration: none; color: inherit;" href="sku-management.php"><h5>SKU Management</h5></a>
            </li>
            <li class="nav-item">
                <a style="text-decoration: none; color: inherit;" href="internal-inventory.php"><h5>Internal Inventory</h5></a>
            </li>
            <li class="nav-item">
                <a style="text-decoration: none; color: inherit;" href="warehouse-inventory.php"><h5>Warehouse Inventory</h5></a>
            </li>
            <li class="nav-item">
                <a style="text-decoration: none; color: inherit;" href="mpl-records.php"><h5>MPL Records</h5></a>
            </li>
            <li class="nav-item nav-item--active">
                <a style="text-decoration: none; color: inherit;" href="order-records.php"><h5>Order Records</h5></a>
            </li>
        </ul>
    </div>

    <div class="main-content" style="position: relative;">
        <a href="order-records.php" class="back-link">Back to List</a>
        <h1 class="color-text-primary"><?= $id ? 'Edit Order' : 'Create Order'; ?></h1>

        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mpl-form-header">
                <div class="form-field">
                    <label>Order Number</label>
                    <input type="text" name="order_number" required value="<?= htmlspecialchars($order['order_number'] ?? '') ?>">
                </div>
                <div class="form-field">
                    <label>Ship To Company</label>
                    <input type="text" name="ship_to_company" value="<?= htmlspecialchars($order['ship_to_company'] ?? '') ?>">
                </div>
                <div class="form-field">
                    <label>Street</label>
                    <input type="text" name="ship_to_street" value="<?= htmlspecialchars($order['ship_to_street'] ?? '') ?>">
                </div>
                <div class="form-field">
                    <label>City</label>
                    <input type="text" name="ship_to_city" value="<?= htmlspecialchars($order['ship_to_city'] ?? '') ?>">
                </div>
                <div class="form-field">
                    <label>State</label>
                    <input type="text" name="ship_to_state" maxlength="2" value="<?= htmlspecialchars($order['ship_to_state'] ?? '') ?>">
                </div>
                <div class="form-field">
                    <label>ZIP</label>
                    <input type="text" name="ship_to_zip" value="<?= htmlspecialchars($order['ship_to_zip'] ?? '') ?>">
                </div>
            </div>

            <div class="units-section">
                <h4>Select Units</h4>
                <table class="units-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all" onclick="toggleAll(this)"></th>
                            <th>Unit #</th>
                            <th>SKU</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($available_units as $unit): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="unit_ids[]" class="unit-checkbox"
                                    value="<?= $unit['unit_number'] ?>"
                                    <?= in_array($unit['unit_number'], $selected_unit_numbers) ? 'checked' : '' ?>>
                            </td>
                            <td><?= $unit['unit_number'] ?></td>
                            <td><?= $unit['sku'] ?></td>
                            <td><?= $unit['description'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($available_units)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;color:#999;">No units available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn btn-primary"><?= $id ? 'Update Order' : 'Create Order' ?></button>
                <a href="order-records.php" class="btn btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAll(source) {
        const checkboxes = document.querySelectorAll('.unit-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = source.checked;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const unitCheckboxes = document.querySelectorAll('.unit-checkbox');
        
        unitCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(unitCheckboxes).every(cb => cb.checked);
                selectAll.checked = allChecked;
            });
        });
    });
</script>
</body>
</html>

<?php $connection->close(); ?>