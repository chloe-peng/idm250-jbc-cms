# API Documentation

## Overview

All endpoints require a valid API key passed via the `x-api-key` header. Authentication is handled by `check_api_key()` from `lib/auth.php`.

---

## Dependencies

| File | Purpose |
|---|---|
| `db_connect.php` | Establishes the database connection, exposes `$connection` and `$env` globals |
| `lib/auth.php` | API key authentication |
| `lib/mpl.php` | MPL business logic (`create_mpl`, `handle_confirm`) |
| `lib/orders.php` | Order business logic (`get_order_by_number`, `get_order_items`, `update_order_status`) |
| `lib/cms.php` | CMS/product logic (`get_products`) |

---

## Functions

### Authentication — `lib/auth.php`

#### `check_api_key($env)`
Validates the API key from the `x-api-key` request header against the expected value in the environment config. Terminates the request with a `401` response if the key is missing or invalid.

| Parameter | Type | Description |
|---|---|---|
| `$env` | array | Environment config array containing the expected API key |

---

### MPL — `lib/mpl.php`

#### `create_mpl($data, $unit_ids)`
Creates a new Master Packing List record in the database and associates the provided unit IDs with it.

| Parameter | Type | Description |
|---|---|---|
| `$data` | array | Associative array containing `reference_number`, `trailer_number`, and `expected_arrival` |
| `$unit_ids` | array | Array of unit ID strings to associate with the MPL |

**Returns:** The new MPL's ID on success, or a falsy value on failure.

---

#### `handle_confirm($ref)`
Looks up an MPL by its reference number and marks it as confirmed.

| Parameter | Type | Description |
|---|---|---|
| `$ref` | string | The reference number of the MPL to confirm |

**Returns:** Outputs a JSON response directly. Returns a `200` on success or an appropriate error code on failure.

---

### Orders — `lib/orders.php`

#### `get_order_by_number($order_number)`
Fetches a single order record from the database matching the given order number.

| Parameter | Type | Description |
|---|---|---|
| `$order_number` | string | The order number to look up |

**Returns:** Associative array of order data (including `id` and `status`), or `null`/`false` if not found.

---

#### `get_order_items($order_id)`
Retrieves all line items associated with a given order.

| Parameter | Type | Description |
|---|---|---|
| `$order_id` | int | The internal database ID of the order |

**Returns:** Array of associative arrays, each containing at minimum a `unit_number` field.

---

#### `update_order_status($order_id, $status, $shipped_at)`
Updates the status and shipped timestamp of an order.

| Parameter | Type | Description |
|---|---|---|
| `$order_id` | int | The internal database ID of the order |
| `$status` | string | The new status value (e.g. `confirmed`) |
| `$shipped_at` | string | Timestamp of when the order was shipped |

**Returns:** Truthy on success, falsy on failure.

---

### Products — `lib/cms.php`

#### `get_products()`
Retrieves all products from the database.

**Returns:** Associative array with keys:

| Key | Type | Description |
|---|---|---|
| `total` | int | Total number of products |
| `products` | array | Array of product records |

Returns a falsy value if no products are found.

---

### Inventory — `inventory.php`

#### `get_inventory()`
Fetches an inventory record. Called as a variable-function in `inventory.php` for retrieving a product by the ID parsed from the URL path.

**Returns:** Product data on success, or a falsy value if not found.

---

## Endpoints

### 1. Inventory — `inventory.php`

Retrieve inventory product details by ID.

**Method:** `GET`

**URL format:** `/inventory/{id}`

**Path Parameters**

| Parameter | Type | Description |
|---|---|---|
| `id` | integer | The inventory item ID to retrieve |

**Responses**

| Status | Description |
|---|---|
| `200 OK` | Product found and returned |
| `404 Not Found` | No product matching the given ID |

**Example Response (200)**
```json
{
  "success": true,
  "product": { ... }
}
```

**Example Response (404)**
```json
{
  "error": "Product not found"
}
```

---

### 2. Create MPL — `mpl.php`

Creates a new Master Packing List (MPL) with associated units.

**Method:** `POST`

**Headers**

| Header | Value |
|---|---|
| `Content-Type` | `application/json` |
| `x-api-key` | Your API key |

**Request Body**

| Field | Type | Required | Description |
|---|---|---|---|
| `reference_number` | string | Yes | Reference number for the MPL |
| `trailer_number` | string | Yes | Trailer number associated with the MPL |
| `expected_arrival` | string | Yes | Expected arrival date/time |
| `unit_ids` | array | Yes | Array of unit IDs to include in the MPL |

**Example Request**
```json
{
  "reference_number": "REF-001",
  "trailer_number": "TRL-456",
  "expected_arrival": "2026-03-20",
  "unit_ids": ["U001", "U002", "U003"]
}
```

**Responses**

| Status | Description |
|---|---|
| `200 OK` | MPL created successfully |
| `400 Bad Request` | Missing required fields or no units provided |
| `500 Internal Server Error` | MPL creation failed |

**Example Response (200)**
```json
{
  "success": true,
  "reference_number": "REF-001",
  "trailer_number": "TRL-456",
  "expected_arrival": "2026-03-20",
  "created_at": "2026-03-13 10:00:00",
  "unit_amount": 3,
  "units": { ... }
}
```

**Example Response (400)**
```json
{
  "error": "Bad Request",
  "details": "Missing required field: reference_number"
}
```

**Example Response (500)**
```json
{
  "error": "Failed to create MPL"
}
```

---

### 3. Manage MPLs — `mpls.php`

Performs actions on existing Master Packing Lists, currently supporting confirmation.

**Method:** `POST`

**Headers**

| Header | Value |
|---|---|
| `Content-Type` | `application/json` |
| `x-api-key` | Your API key |

**Request Body**

| Field | Type | Required | Description |
|---|---|---|---|
| `action` | string | Yes | Action to perform. Currently supported: `confirm` |
| `reference_number` | string | Yes | Reference number of the MPL to act on |

**Supported Actions**

| Action | Description |
|---|---|
| `confirm` | Confirms the MPL identified by the given reference number |

**Example Request**
```json
{
  "action": "confirm",
  "reference_number": "REF-001"
}
```

**Responses**

| Status | Description |
|---|---|
| `200 OK` | Action performed successfully |
| `400 Bad Request` | Missing `action` or `reference_number`, or unknown action |

**Example Response (400 — unknown action)**
```json
{
  "success": false,
  "error": "Unknown action: revert"
}
```

**Example Response (400 — missing fields)**
```json
{
  "success": false,
  "error": "Missing action or reference_number"
}
```

---

### 4. Orders — `orders.php`

Marks an order as shipped, updates its status to confirmed, and removes its units from inventory.

**Method:** `POST`

**Headers**

| Header | Value |
|---|---|
| `Content-Type` | `application/json` |
| `x-api-key` | Your API key |

**Request Body**

| Field | Type | Required | Description |
|---|---|---|---|
| `action` | string | Yes | Action to perform. Currently supported: `ship` |
| `order_number` | string | Yes | The order number to act on |
| `shipped_at` | string | Yes | Timestamp of when the order was shipped |

**Example Request**
```json
{
  "action": "ship",
  "order_number": "ORD-789",
  "shipped_at": "2026-03-13 08:30:00"
}
```

**Responses**

| Status | Description |
|---|---|
| `200 OK` | Order shipped and confirmed, or was already confirmed |
| `400 Bad Request` | Unknown action |
| `404 Not Found` | Order number not found |
| `405 Method Not Allowed` | Non-POST request received |
| `422 Unprocessable` | Order has no associated units |

**Example Response (200)**
```json
{
  "success": true,
  "order_number": "ORD-789",
  "shipped_at": "2026-03-13 08:30:00",
  "confirmed_at": "2026-03-13 10:00:00",
  "units_deleted": 4
}
```

**Example Response (200 — already confirmed)**
```json
{
  "success": true,
  "details": "Order already confirmed"
}
```

**Example Response (404)**
```json
{
  "error": "Not Found",
  "details": "Order not found: ORD-789"
}
```

**Example Response (422)**
```json
{
  "error": "Unprocessable",
  "details": "No units found for this order"
}
```

**Notes**
- When an order is shipped, all associated unit records are permanently deleted from the `inventory` table using `DELETE FROM inventory WHERE unit_number = ?`.
- If the order status is already `confirmed`, the endpoint returns `200` without reprocessing.

---

### 5. Products — `products.php`

Retrieves all products.

**Method:** `GET`

**Headers**

| Header | Value |
|---|---|
| `x-api-key` | Your API key |

**Responses**

| Status | Description |
|---|---|
| `200 OK` | Products retrieved successfully |
| `404 Not Found` | No products found |

**Example Response (200)**
```json
{
  "success": true,
  "total_products": 42,
  "products": [ ... ]
}
```

**Example Response (404)**
```json
{
  "error": "Products not found"
}
```

---

## Common Errors

| Status | Meaning |
|---|---|
| `400 Bad Request` | The request is missing required fields or contains an invalid action |
| `401 Unauthorized` | API key is missing or invalid |
| `404 Not Found` | The requested resource does not exist |
| `405 Method Not Allowed` | An unsupported HTTP method was used |
| `422 Unprocessable` | The request is valid but cannot be processed due to data state |
| `500 Internal Server Error` | A server-side error occurred |
