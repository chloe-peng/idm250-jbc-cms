# Products API Documentation

## Overview

The `products.php` API endpoint retrieves product data from the database.  
It connects to `lib/cms.php`, which contains the `get_products()` function responsible for querying the database.

---

## File Structure

```
/api/products.php
/lib/cms.php
```

- `products.php` → API endpoint
- `lib/cms.php` → Contains database connection and `get_products()` function


### GET /products.php

Retrieves all products from the `cms_products` table.

---

## Internal Function

Located in `lib/cms.php`:

```php
function get_products() {
    global $connection;

    $stmt = $connection->prepare("SELECT sku, description, uom, piece, length, width, height, weight FROM cms_products");
    if($stmt->execute()) {
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);

        return [
            'total' => count($products),
            'products' => $products
        ];
    } else {
        return false;   
    }
}
```

---

## Database Table

### Table: `cms_products`

| Column      | Type (Example) |
|------------|---------------|
| sku        | VARCHAR       |
| description| TEXT          |
| uom        | VARCHAR       |
| piece      | INT           |
| length     | DECIMAL       |
| width      | DECIMAL       |
| height     | DECIMAL       |
| weight     | DECIMAL       |

---

### Success Response (JSON)

```json
{
  "total": 2,
  "products": [
    {
      "sku": "ABC123",
      "description": "Sample Product",
      "uom": "PCS",
      "piece": 1,
      "length": 10,
      "width": 5,
      "height": 2,
      "weight": 0.5
    }
  ]
}
```

### Error Response

If the query fails:

```json
false
```

---

## Response Fields

| Field      | Type    | Description |
|------------|---------|------------|
| total      | integer | Total number of products returned |
| products   | array   | List of product objects |
| sku        | string  | Product SKU |
| description| string  | Product description |
| uom        | string  | Unit of measure |
| piece      | integer | Quantity per unit |
| length     | float   | Product length |
| width      | float   | Product width |
| height     | float   | Product height |
| weight     | float   | Product weight |


