<?php
require_once 'Item.php';

class ItemTest {
    private $db;

    public function __construct() {
        try {
            $dbInstance = new DB();
            $this->db = $dbInstance->connect(); 
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function testItemCrud() {
        try {   
    
            // Test data for creating an item
            $createData = [
                'id' => 1,
                'title' => 'Test Item',
                'description' => ['main' => 'This is a test item description.'],
                'item_price' => 10.00,
                'brand' => 'TestBrand',
                'category_id' => 101,
                'img_arr' => ['1' => 'https://example.com/test-image.jpg'],
                'available_stock' => 50
            ];

            $item = new Item($this->db);
            // Test Create
            // Insert the test item into the database
            $createResult = $item->create($createData);
            
            if (!$createResult) {
                throw new Exception("Failed to create test item.");
            }

            echo ("\n Create item Passtd");
    
            // Test data for updating the item
            $updateData = [
                'id' =>  $createData['id'],
                'name' => 'Updated Item2',
                'price' => 40.25,
                'stock' => 20
            ];
            
            // Test Update
            // Call the update method
            $item->update($updateData);
    
            // Test Filter (fetch)
            $filterResult = $item->filter(['id' => $createData['id']]);
           
            // Verify the updates

            foreach ($updatedItem as $field => $updatedField)
            {   
                if($updatedItem[$field] != $updatedField)
                {
                    echo("Item was not updated correctly");
                }
            }
                
            echo "\nUpdate Item passed\n";

          // Clean up: delete the test item
            $item->delete($updateData['id']);

            echo "\nDelete Item passed\n";

        } catch (Exception $e) {
            echo "Test failed: " . $e->getMessage();
        }
    }

    public function testSort() {
        $itemInstance = new Item($this->db);
        var_dump($itemInstance->sort('id', 'ASC'));
    }
}
// Instantiate the test class and run the test
$test = new ItemTest();
$test->testSort();
