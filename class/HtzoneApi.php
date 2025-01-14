<?php

require_once 'DB.php';
require_once 'Item.php';
require_once 'Category.php';

class HtzoneApi {
    private $db;
    private $base_url = 'https://storeapi.htzone.co.il/ext/O2zfcVu2t8gOB6nzSfFBu4joDYPH7s';

    public function __construct() {
        $dbInstance = new DB();
        $this->db = $dbInstance->connect(); 

        $this->initDatabase();
    }

    public function initDatabase() {
        // Create categories table
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                description TEXT,
                parent_id INTEGER,
                FOREIGN KEY (parent_id) REFERENCES categories(id)
            )
        ');

        // Create items table
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS items (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                description TEXT,
                price REAL NOT NULL,
                brand TEXT,
                category_id INTEGER,
                image_url TEXT,
                stock INTEGER,
                FOREIGN KEY (category_id) REFERENCES categories(id)
            )
        ');
    }

    /* This function gets an endpoint of a url and returns the data that stores inside. */
    private function makeRequest($endpoint) {
        $curl = curl_init();
        $url = $this->base_url . $endpoint;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPGET, true);

        // Added for ssl verification pass
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            curl_close($curl);
            throw new Exception('cURL error: ' . curl_error($curl));
        }
    
        // Close the cURL session
        curl_close($curl);

        // Decode and return the JSON response
        $data = json_decode($response, true);

        if ($data === null) {
            throw new Exception('Failed to decode JSON response from API.');
        }

        return $data;
    }

    /* This function fetches Items from API using CURL and store it to local db */
    public function fetchAndStoreItems($category_id) {

        if (empty($category_id)) {
            throw new Exception("Required category_id is missing.");
        }       

        try {
            // Fetch items from API using makeRequest
            $items = $this->makeRequest('/items/' . $category_id);
            if (!is_array($items) || !isset($items['data'])) {
                throw new Exception("Invalid response from API: " . json_encode($items));
            }

         // Iterate over items
         foreach ($items['data'] as $item) {
           
            if (empty($item['id']) || empty($item['title'])) {
                error_log("Skipping invalid item (missing required fields): " . json_encode($item));
                continue;
            }
          
            $item_instance = new Item($this->db, $item['id']); 
            $item_instance->create($item);
            
        }
            echo "Items fetched and stored successfully.";
        } catch (Exception $e) {
            echo "Error fetching and storing items: " . $e->getMessage();
        }
            
    }

    /* This function fetches categories from API using CURL and store it to local db */
    public function fetchAndStoreCategories() {
        try {
            $categories = $this->makeRequest('/categories');
            if (!is_array($categories) || !isset($categories['data'])) {
                throw new Exception("Invalid response from API: " . json_encode($categories));
            }

            foreach ($categories['data'] as $category) {
            
                // Check for required fields
                if (empty($category['category_id']) || empty($category['title'])) {
                    error_log("Skipping invalid category: Missing category_id or title - " . json_encode($category));
                    continue;
                }
                
                $categoryInstance = new Category ($this->db, (int)$category['category_id']);
                $categoryInstance->create($category);

                // Getting the items for each category
                $this->fetchAndStoreItems((int)$category['category_id']);

            }
        
    
        echo "Categories fetched and stored successfully.";
        } catch (Exception $e) {
        echo "Error fetching and storing categories: " . $e->getMessage();
        }
    }
}



