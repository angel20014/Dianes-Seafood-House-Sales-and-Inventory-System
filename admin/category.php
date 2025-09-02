<?php
include('db.php'); // Include your database connection

// Fetch categories and products from the database
$categorySql = "SELECT DISTINCT category FROM products ORDER BY category";
$categoryResult = $conn->query($categorySql);
?>

<!-- Category View Section -->
<div id="categoryView" class="section" style="display: none;">
    <form method="POST" action="record_category.php">
        <table id="categoryTable">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Product Code</th>
                    <th>Product Name</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $categorySql = "SELECT DISTINCT category FROM products ORDER BY category";
                $categoryResult = $conn->query($categorySql);
                if ($categoryResult->num_rows > 0) {
                    while ($categoryRow = $categoryResult->fetch_assoc()) {
                        $category = $categoryRow['category'];
                        $productQuery = "SELECT product_code, product_name FROM products WHERE category = '$category'";
                        $productResult = $conn->query($productQuery);
                        if ($productResult->num_rows > 0) {
                            echo "<tr class='category-header'>
                                <th colspan='3'>" . htmlspecialchars($category) . "</th>
                            </tr>";
                            while ($productRow = $productResult->fetch_assoc()) {
                                echo "<tr>
                                    <td>
                                        <input type='hidden' name='categories[]' value='$category'>
                                        <input type='hidden' name='product_codes[]' value='{$productRow['product_code']}'>
                                        <input type='hidden' name='product_names[]' value='{$productRow['product_name']}'>
                                        " . htmlspecialchars($category) . "
                                    </td>
                                    <td>" . htmlspecialchars($productRow['product_code']) . "</td>
                                    <td>" . htmlspecialchars($productRow['product_name']) . "</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No products found for this category.</td></tr>";
                        }
                    }
                } else {
                    echo "<tr><td colspan='3'>No categories found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </form>
</div>
