<!-- Product List Section -->
<div id="productList" class="section" style="display: block;">
    <div class="header-container">
        <h3>PRODUCT LIST</h3>
        <button onclick="showAddProductModal()">+ Product</button>
    </div>
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Code</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Current Stock</th>
                <th>Unit</th>
                <th>Price</th>
                <th>Expiration Date</th>
                <th>Tools</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include 'db.php';
            $productsSql = "
                SELECT p.product_id, p.product_code, p.product_name, p.category,
                       COALESCE(SUM(s.stock_in), 0) AS total_stock_in,
                       COALESCE(SUM(s.stock_out), 0) AS total_stock_out,
                       COALESCE(SUM(sa.quantity), 0) AS total_sold,
                       p.unit, p.price, p.expiration_date
                FROM products p
                LEFT JOIN stock s ON p.product_id = s.product_id
                LEFT JOIN sales sa ON p.product_name = sa.product_name
                GROUP BY p.product_id, p.product_code, p.product_name, p.category, p.unit, p.price, p.expiration_date
            ";
            $result = $conn->query($productsSql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $total_stock_out = $row['total_stock_out'] + $row['total_sold'];
                    $current_stock = $row['total_stock_in'] - $total_stock_out;

                    echo "<tr>
                        <td>{$row['product_id']}</td>
                        <td>{$row['product_code']}</td>
                        <td>{$row['product_name']}</td>
                        <td>{$row['category']}</td>
                        <td id='stock-{$row['product_id']}'>" . htmlspecialchars($current_stock) . "</td>
                        <td>{$row['unit']}</td>
                        <td>{$row['price']}</td>
                        <td>{$row['expiration_date']}</td>
                        <td>
                            <button onclick='editProduct({$row['product_id']})' class='action-btn'>
                                <i class='fas fa-edit'></i>
                            </button>
                            <button onclick='viewProduct({$row['product_id']})' class='action-btn'>
                                <i class='fas fa-eye'></i>
                            </button>
                            <button onclick='deleteProduct({$row['product_id']})' class='action-btn'>
                                <i class='fas fa-trash'></i>
                            </button>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='9' class='no-products'>No products available</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- View Product Modal -->
<div id="viewProductModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeViewProductModal()">&times;</span>
        <h2>Product Details</h2>
        <p><strong>Product Code:</strong> <span id="viewProductCode"></span></p>
        <p><strong>Product Name:</strong> <span id="viewProductName"></span></p>
        <p><strong>Category:</strong> <span id="viewCategory"></span></p>
        <p><strong>Unit:</strong> <span id="viewUnit"></span></p>
        <p><strong>Price:</strong> <span id="viewPrice"></span></p>
        <p><strong>Expiration Date:</strong> <span id="viewExpirationDate"></span></p>
    </div>
</div>
