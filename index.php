<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'class/DB.php';
require_once 'class/Item.php';
require_once 'class/Category.php';

$dbInstance = new DB();
$db = $dbInstance->connect(); 
$itemInstance = new Item($db);
$categoryInstance = new Category($db);
$categories = array_slice($categoryInstance->getCategories(), 0, 3);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTZone PHP Test</title>
    <link rel="stylesheet" href="static/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header>
            <h1>HTZone Sale</h1>
        </header>

        <main>
            <!-- Carousels Section -->
            <section class="carousels-wrapper">
                <?php foreach ($categories as $category): ?>
                    <?php $items = $categoryInstance->getCategoryItems($category['id']); ?>
                    <div id="carousel-<?php echo $category['id']; ?>" class="carousel-container">
                        <h2><?php echo htmlspecialchars($category['name']); ?></h2>
                        <div class="carousel-items">
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $product): ?>
                                    <div class="carousel-item">
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <p>$<?php echo number_format($product['price'], 2); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button class="carousel-btn left-btn" data-direction="left" data-carousel-id="carousel-<?php echo $category['id']; ?>">&#10094;</button>
                        <button class="carousel-btn right-btn" data-direction="right" data-carousel-id="carousel-<?php echo $category['id']; ?>">&#10095;</button>
                    </div>
                <?php endforeach; ?>
            </section>

            <!-- Filters Section -->
            <section class="filters-wrapper">
                <select id="category-filter">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['id']); ?>">
                            <?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="sort-select">
                    <option value="name-ASC">Name (A-Z)</option>
                    <option value="name-DESC">Name (Z-A)</option>
                    <option value="price-ASC">Price (Low to High)</option>
                    <option value="price-DESC">Price (High to Low)</option>
                </select>
                <form id="price-filter">
                    <input type="number" id="price-min" placeholder="Min Price">
                    <input type="number" id="price-max" placeholder="Max Price">
                    <button type="submit">Apply</button>
                </form>
            </section>

            <!-- Product List Section -->
            <section class="products-wrapper">
                <div id="product-list" class="grid-layout"></div>
                <div id="loading-indicator">Loading...</div>
            </section>
        </main>
    </div>
    <script src="static\js\scripts.js"></script>
</body>
</html>
