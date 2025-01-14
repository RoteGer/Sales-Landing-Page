<?php
require_once 'Category.php';

class CategoryTest {
    private $db;

    public function __construct() {
        try {
            $dbInstance = new DB();
            $this->db = $dbInstance->connect(); 
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function testGetCategories() {
        $categoryInstance = new Category($this->db);
        var_dump($categoryInstance->getCategories());
    }

    public function testGetCategoryById() {
        $categoryInstance = new Category($this->db);
        var_dump($categoryInstance->getCategoryById(1042));
    }

    public function testGetCategoryItems() {
        $categoryInstance = new Category($this->db);
        var_dump($categoryInstance->getCategoryItems(2626));
    }

    public function testGetTopCategories() {
        $categoryInstance = new Category($this->db);
        var_dump($categoryInstance->getTopCategories(1));
    }

   
}

// Instantiate the test class and run the test
$test = new CategoryTest();
$test->testGetTopCategories();

