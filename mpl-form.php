<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'db_connect.php';
require './lib/auth.php';
require './lib/mpl.php';

require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mpl = $id ? get_mpl($id) : [];

// check if editing a non-draft MPL
if ($id && $mpl && $mpl['status'] !== 'draft') {
    $_SESSION['error'] = 'Only draft MPLs can be edited.';
    header('Location: mpl-records.php');
    exit;
} 

// handles form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'reference_number' => $_POST['reference_number'],
        'trailer_number'   => $_POST['trailer_number'],
        'expected_arrival' => $_POST['expected_arrival']
    ];
    
    $unit_ids = isset($_POST['unit_ids']) ? $_POST['unit_ids'] : [];
    
    if (empty($data['reference_number']) || empty($unit_ids)) {
        $error = "Reference number and at least one unit are required.";
    } else {
        if ($id) {
            $result = update_mpl($id, $data, $unit_ids);
        } else {
            $result = create_mpl($data, $unit_ids);
        }
        
        if ($result) {
            $_SESSION['success'] = $id ? 'MPL updated successfully.' : 'MPL created successfully.';
            header('Location: mpl-records.php');
            exit;
        } else {
            $error = 'Error unable to create the MPL.';
        }
    }
}

// get inventory units for selection
$inventory_query = "SELECT i.unit_number, i.ship_date, i.ficha, i.description, s.sku
                    FROM inventory i 
                    LEFT JOIN cms_products s ON i.ficha = s.ficha
                    WHERE i.location = 'internal'
                    ORDER BY i.ship_date DESC";
$inventory_result = $connection->query($inventory_query);
$available_units = $inventory_result->fetch_all(MYSQLI_ASSOC);

// get currently selected unit IDs if editing
$selected_unit_ids = [];
if ($id) {
    $selected_items = get_mpl_items($id);
    foreach ($selected_items as $item) {
        $selected_unit_ids[] = $item['unit_number'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id ? 'Edit' : 'Create'; ?> MPL - JBC Manufacturing CMS</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/sku.css">
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/mpl-form.css">
</head>

<body>
    <!-- header -->
    <div class="header-bar">
        <h2>JBC Manufacturing CMS</h2>
        <div class="header-bar-right">
            <h5><?php echo htmlspecialchars($_SESSION['user_email']); ?></h5>
            <a href="logout.php" style="text-decoration: none; color: inherit;"><h5>Logout</h5></a>
        </div>
    </div>

    <!-- page wrapper: sidebar + main content -->
    <div class="page-wrapper">
        <!-- sidebar -->
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
                <li class="nav-item nav-item--active">
                    <a style="text-decoration: none; color: inherit;" href="mpl-records.php"><h5>MPL Records</h5></a>
                </li>
                <li class="nav-item">
                    <a style="text-decoration: none; color: inherit;" href="order-records.php"><h5>Order Records</h5></a>
                </li>
            </ul>
        </div>

        <!-- main content -->
        <div class="main-content" style="position: relative;">
            <a href="mpl-records.php" class="back-link">Back to List</a>
            
            <h1 class="color-text-primary" style="margin-bottom: 30px;">
                <?php echo $id ? 'Edit MPL' : 'Create MPL'; ?>
            </h1>

            <?php if (isset($error)): ?>
                <div style="background-color: #ffebee; color: #c62828; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <h4 style="margin-bottom: 20px; color: #2C77A0;">
                    <?php echo $id ? 'Edit MPL' : 'Create an MPL'; ?>
                </h4>

                <div class="mpl-form-header">
                    <div class="form-field">
                        <label for="reference_number">Reference Number</label>
                        <input 
                            type="text" 
                            id="reference_number" 
                            name="reference_number" 
                            placeholder="Fill reference number"
                            value="<?= htmlspecialchars($mpl['reference_num'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="trailer_number">Trailer Number</label>
                        <input 
                            type="text" 
                            id="trailer_number" 
                            name="trailer_number" 
                            placeholder="Fill trailer number"
                            value="<?= htmlspecialchars($mpl['trailer_number'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="expected_arrival">Expected Arrival</label>
                        <input 
                            type="date" 
                            id="expected_arrival" 
                            name="expected_arrival" 
                            placeholder="Fill expected arrival"
                            value="<?= htmlspecialchars($mpl['expected_arrival'] ?? '') ?>"
                            required
                        >
                    </div>
                </div>

                <div class="units-section">
                    <h4>Select Units to Transfer</h4>

                    <table class="units-table">
                        <thead>
                            <tr>
                                <th class="checkbox-cell"></th>
                                <th>Unit ID</th>
                                <th>SKU</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- select all row -->
                            <tr class="select-all-row">
                                <td class="checkbox-cell">
                                    <input type="checkbox" id="select-all" onclick="toggleAll(this)">
                                </td>
                                <td colspan="3">Select All</td>
                            </tr>

                            <!-- inventory units -->
                            <?php foreach ($available_units as $unit): ?>
                            <tr>
                                <td class="checkbox-cell">
                                    <input type="checkbox" name="unit_ids[]" 
                                        value="<?= htmlspecialchars($unit['unit_number']) ?>" class="unit-checkbox"
                                        <?= in_array($unit['unit_number'], $selected_unit_ids) ? 'checked' : '' ?>>
                                </td>
                                <td><?= htmlspecialchars($unit['unit_number']) ?></td>
                                <td><?= htmlspecialchars($unit['sku']) ?></td>
                                <td><?= htmlspecialchars($unit['description']) ?></td>
                            </tr>
                            <?php endforeach; ?>

                            <?php if (empty($available_units)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #999;">
                                    No inventory units available
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary">
                        <?= $id ? 'Update MPL' : 'Create MPL' ?>
                    </button>
                    <a href="mpl-records.php" class="btn btn-cancel">Cancel</a>
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
<?php
$connection->close();
?>