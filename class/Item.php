<?php

class Item {
    private $db;
    private $id;
    private $name;
    private $description;
    private $price;
    private $brand;
    private $category_id;
    private $image_url;
    private $stock;

    // Constructor to initialize the item with a database connection
    public function __construct($db, $id = null, $name = null, $description = null, $price = null, $brand = null, $category_id = null, $image_url = null, $stock = null) {
        if (empty($db)) {
            throw new Exception("Database connection is required.");
        }

        $this->db = $db;
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->brand = $brand;
        $this->category_id = $category_id;
        $this->image_url = $image_url;
        $this->stock = $stock;
    }

    // Getters and Setters for the item id
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    /* Insert to db */
    public function create($data) {
        try {
            // Validate the data array to ensure all required keys exist
            $requiredFields = ['id', 'title'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new Exception("Failed to insert item, missing: " . $field);
                }
            }
 
            // Prepare the SQL statement
            $query = $this->db->prepare("
                INSERT INTO items (id, name, description, price, brand, category_id, image_url, stock)
                VALUES (:id, :name, :description, :price, :brand, :category_id, :image_url, :stock)
                ON CONFLICT(id) DO NOTHING
            ");

            if (!$query) {
                throw new Exception("Query preparation failed: " . $this->db->lastErrorMsg());
            }

            // Prepare data to be bound to query
            $item_id = (int)$data['id'];
            $name = $data['title'] ?? null;
            $description = $data['description']['main'] ?? null;
            $price = isset($data['item_price']) ? (float)$data['item_price'] : null;
            $brand = $data['brand'] ?? null;
            $category_id = (int)($data['category_id'] ?? null); 
            $image_url = $data['img_arr']['1'] ?? null;
            $stock = isset($data['available_stock']) ? (int)$data['available_stock'] : null;
            
            // Bind data to prepared statement
            $query->bindValue(':id', $item_id, SQLITE3_INTEGER);
            $query->bindValue(':name', $name, SQLITE3_TEXT);
            $query->bindValue(':description', $description, SQLITE3_TEXT);
            $query->bindValue(':price', $price, SQLITE3_FLOAT);
            $query->bindValue(':brand', $brand, SQLITE3_TEXT);
            $query->bindValue(':category_id', $category_id, SQLITE3_INTEGER);
            $query->bindValue(':image_url', $image_url, SQLITE3_TEXT);
            $query->bindValue(':stock', $stock, SQLITE3_INTEGER);
            $this->db->exec('PRAGMA foreign_keys = OFF');
            // Execute the query
            $result = $query->execute();
            $this->db->exec('PRAGMA foreign_keys = ON');



            if (!$result) {
                throw new Exception("Failed to insert item: " . $this->db->lastErrorMsg());
            }

            return $item_id;

        } catch (Exception $e) {
            error_log("Error in Item->create: " . $e->getMessage());
            return false;
        }
    }

    /* Update one or more fields for a specific item ID */
    public function update($data) {
        try {            
            // Ensure the item has a valid ID
            if (empty($this->id) && !isset($data['id'])) {
                throw new Exception("Failed to update item: Missing item ID.");
            }
    
            // Validate that the provided data is an array and not empty
            if (!is_array($data) || empty($data)) {
                throw new Exception("Failed to update item: Invalid data.");
            }
    
            // Validate that all provided fields are valid
            $validFields = ['id', 'name', 'description', 'price', 'brand', 'category_id', 'image_url', 'stock'];
            foreach ($data as $field => $value) {
                if (!in_array($field, $validFields)) {
                    throw new Exception("Failed to update item: Invalid field - $field.");
                }
            }
    
            // Dynamically build the SET clause of the SQL query
            $setClauses = [];
            foreach ($data as $field => $value) {
                if ($field != 'id'){
                $setClauses[] = "$field = :$field";
                $params[":$field"] = $value;
                }
            }
            // Prepare the SQL update query with the dynamically built SET clause
            $query = $this->db->prepare("
                UPDATE items SET
                    " . implode(', ', $setClauses) ."
                WHERE id = :id
            ");

             // Bind values to the query
            foreach ($params as $param => $value) {
                if (is_int($value)) {
                    $query->bindValue($param, $value, SQLITE3_INTEGER);
                } elseif (is_float($value)) {
                    $query->bindValue($param, $value, SQLITE3_FLOAT);
                } elseif (is_string($value)) {
                    $query->bindValue($param, $value, SQLITE3_TEXT);
                } elseif (is_null($value)) {
                    $query->bindValue($param, $value, SQLITE3_NULL);
                } else {
                    throw new Exception("Unsupported value type for field $param");
                }
            }

            // Bind the ID to the query
            $query->bindValue(':id', $data['id'], SQLITE3_INTEGER);

            $this->db->exec('PRAGMA foreign_keys = OFF');
            // Execute the query
            $result = $query->execute();
            $this->db->exec('PRAGMA foreign_keys = ON');

            if (!$result) {
                throw new Exception("Failed to Update item: " . $this->db->lastErrorMsg());
            }

            return "Updated succesfully";
    
        } catch (Exception $e) {
            // Handle any exceptions by logging the error and rethrowing the exception
            error_log("Error in Item::updateFields: " . $e->getMessage());
            throw $e;  // Rethrow the exception for handling elsewhere in the application
        }
    }

    /* Delet row by item ID */
    public function delete($id) {
        try {
            // Validate the ID to ensure it is a valid integer
            if (empty($id) && !is_numeric($id)) {
                throw new Exception("Invalid ID provided for deletion.");
            }

            // Check if the item exists before attempting to delete it
            $checkQuery = $this->db->query("SELECT 1 FROM items WHERE id = $id LIMIT 1");

            // If the item doesn't exist, throw an exception
            if (!$checkQuery) {
                throw new Exception("Item with ID $id does not exist.");
            }

            // Prepare the DELETE query
            $deleteQuery = $this->db->query("DELETE FROM items WHERE id = $id");
    
            if (!$deleteQuery) {
                throw new Exception("Failed to delete item with ID $id.");
            }
  
            // Return success message
            return "Item with ID $id successfully deleted.";
    
        } catch (Exception $e) {
            // Handle exceptions by logging the error and rethrowing the exception
            error_log("Error in Item::delete: " . $e->getMessage());
            return false;  // Indicate failure to delete the item
        }
    }

    /* Filter items based on provided criteria */
    public function filter($criteria) {
        try {
            $whereClauses = [];
    
            // Build the WHERE clause dynamically based on the criteria
            if (isset($criteria['id'])) {
                $whereClauses[] = "id = " . intval($criteria['id']);  // Sanitize integer values
            }
    
            if (isset($criteria['name'])) {
                $whereClauses[] = "name LIKE '%" . addslashes($criteria['name']) . "%'";  // Sanitize string values
            }
    
            if (isset($criteria['brand'])) {
                $whereClauses[] = "brand = '" . addslashes($criteria['brand']) . "'";  // Sanitize string values
            }
    
            if (isset($criteria['category_id'])) {
                $whereClauses[] = "category_id = " . intval($criteria['category_id']);  // Sanitize integer values
            }
    
            if (isset($criteria['min_price'])) {
                $whereClauses[] = "price >= " . floatval($criteria['min_price']);  // Sanitize float values
            }
    
            if (isset($criteria['max_price'])) {
                $whereClauses[] = "price <= " . floatval($criteria['max_price']);  // Sanitize float values
            }

            // Create the SQL query with the WHERE clause
            $sql = "SELECT * FROM items";
            if (count($whereClauses) > 0) {
                $sql .= " WHERE " . implode(" AND ", $whereClauses);
            }
    
            // Disable foreign key checks before executing the query
            $this->db->exec('PRAGMA foreign_keys = OFF');

            // Execute the query directly
            $returned_set  = $this->db->query($sql);

            // Re-enable foreign key checks after query execution
            $this->db->exec('PRAGMA foreign_keys = ON');
    
            // Fetch all results as an associative array
            $resArr = [];
            while ($result = $returned_set->fetchArray(SQLITE3_ASSOC)) {
                $resArr[] = $result;
            }
    
            // If no results were returned
            if (empty($resArr)) {
                throw new Exception("No results found.");
            }
    
            return $resArr;
    
        } catch (Exception $e) {
            // Enable error reporting for debugging
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
    
            // Log the error message and display it
            error_log("Error in filter function: " . $e->getMessage());
            throw new Exception("Error filtering items: " . $e->getMessage());
        }
    }
    
     /* Method to sort and return items based on a given field and order */
     public function sort($field = 'name', $order = 'ASC') {
        try {
            // Validate the field and order to prevent SQL injection
            $validFields = ['id','name', 'price', 'brand', 'category_id'];
            $validOrder = ['ASC', 'DESC'];

            if (!in_array(strtolower($field), $validFields)) {
                throw new Exception("Invalid field for sorting.");
            }

            if (!in_array(strtoupper($order), $validOrder)) {
                throw new Exception("Invalid order for sorting.");
            }

            $sql = "SELECT * FROM items ORDER BY $field $order";
            $returned_set = $this->db->query($sql);

            // Fetch all results as an associative array
            $resArr = [];
            while ($result = $returned_set->fetchArray(SQLITE3_ASSOC)) {
                $resArr[] = $result;
            }
    
            // If no results were returned
            if (empty($resArr)) {
                throw new Exception("No results found.");
            }
    
            return $resArr;
          
        } catch (Exception $e) {
            throw new Exception("Error sorting items: " . $e->getMessage());
        }
    }

}