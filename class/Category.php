<?php

class Category {
    private $db;

    public $id;
    public $name;
    public $description;

    /* Constructor to initialize the category with a database connection */
    public function __construct($db) {
        if (empty($db)) {
            throw new Exception("Database connection is required.");
        }

        $this->db = $db;
    }

      /* Insert to db */
    public function create($data) {
        try {
            // Validate the data array to ensure all required keys exist
            $requiredFields = ['category_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new Exception("Failed to insert category, missing: " . $field);
                }
            }

            // Prepare the SQL statement
            $query = $this->db->prepare("
                INSERT INTO categories (id, name, description, parent_id)
                VALUES (:id, :name, :description, :parent_id)
                ON CONFLICT(id) DO NOTHING
            ");

            if (!$query) {
                throw new Exception("Query preparation failed: " . $this->db->lastErrorMsg());
            }

            // Prepare data to be bound to query
            $id = (int)$data['category_id'];
            $name = $data['title'] ?? null;
            $description = $data['parent_title'] ?? null;
            $parent_id = (int)$data['parent_id'];
            
            // Bind data to prepared statement
            $query->bindValue(':id', $id, SQLITE3_INTEGER);
            $query->bindValue(':name', $name, SQLITE3_TEXT);
            $query->bindValue(':description', $description, SQLITE3_TEXT);
            $query->bindValue(':parent_id', $parent_id, SQLITE3_INTEGER);

            $this->db->exec('PRAGMA foreign_keys = OFF');
            // Execute the query
            $result = $query->execute();
            $this->db->exec('PRAGMA foreign_keys = ON');

            if (!$result) {
                throw new Exception("Failed to insert category: " . $this->db->lastErrorMsg());
            }

            return $id;

        } catch (Exception $e) {
            error_log("Error in Category->create: " . $e->getMessage());
            return false;
        }
    }

    /* Fetch all categories */
    public function getCategories() {
        try {
           
            // Create the SQL query with the WHERE clause
            $sql = "SELECT * FROM categories";

            $returned_set  = $this->db->query($sql);

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
            throw new Exception("Error fetching categories: " . $e->getMessage());
        }
    }

    /* Get specific category by ID */
    public function getCategoryById($id) {
        try {
            if (empty($id)) {

                throw new Exception("Category ID is required.");
            }
            $sql = "SELECT * FROM categories WHERE id = $id";

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
            throw new Exception("Error fetching category by ID: " . $e->getMessage());
        }
    }

    /* Get items for a specific category */
    public function getCategoryItems($category_id) {
        try {
            if (empty($category_id)) {
                throw new Exception("Category ID is required.");
            }

            $sql = "SELECT * FROM items WHERE category_id = $category_id limit 5";

            $returned_set = $this->db->query($sql);

            // Fetch all results as an associative array
            $resArr = [];
            while ($result = $returned_set->fetchArray(SQLITE3_ASSOC)) {
                $resArr[] = $result;
            }

            // If no results were returned
            if (empty($resArr)) {
                return ("No results found.");
            }
   
            return $resArr;
            
        } catch (Exception $e) {
            throw new Exception("Error fetching category by ID: " . $e->getMessage());
        }
    }

    /* Get top categories with the most items */
    public function getTopCategories($limit = 5) {
        if ($limit <= 0 || !is_numeric($limit)) {
            return "limit must be a number and more than 0";
        }

        try {

            $sql = "
                SELECT c.id, c.name, c.description, COUNT(i.id) AS item_count
                FROM categories c
                LEFT JOIN items i ON c.id = i.category_id
                GROUP BY c.id
                ORDER BY item_count DESC
                LIMIT $limit
            ";
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
            throw new Exception("Error fetching top categories: " . $e->getMessage());
        }
    }
}
