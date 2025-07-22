<?php
<<<<<<< Updated upstream
include('../includes/db_connect.php'); // This defines $pdo
include('../includes/header.php');

// Fetch products from DB using PDO
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY product_name ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch products failed: " . $e->getMessage());
    die("Could not load products.");
}
=======
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/db_connect.php';
    require_once __DIR__ . '/../includes/auth.php';
include('../includes/header.php');


// Fetch products from DB
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY product_name ASC");
>>>>>>> Stashed changes
?>

<!DOCTYPE html>
<html>
<head>
    <title>New Sale</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        let productData = {};

        function addProductRow() {
            const productList = JSON.parse(JSON.stringify(productData));
            const container = document.getElementById('product-rows');
            const row = document.createElement('div');
            row.className = 'product-row';

            let options = '';
            for (let id in productList) {
                const product = productList[id];
                options += `<option value="${id}" data-price="${product.price}">${product.name}</option>`;
            }

            row.innerHTML = `
                <select name="product_ids[]" onchange="updatePrice(this)">
                    <option value="">-- Select Product --</option>
                    ${options}
                </select>
                <input type="number" name="quantities[]" placeholder="Quantity" min="1" oninput="calculateSubtotal(this)">
                <input type="text" class="price" readonly placeholder="Price">
                <input type="text" class="subtotal" readonly placeholder="Subtotal">
                <button type="button" onclick="removeRow(this)">Remove</button>
            `;
            container.appendChild(row);
        }

        function removeRow(btn) {
            btn.parentElement.remove();
            calculateTotal();
        }

        function updatePrice(select) {
            const price = select.options[select.selectedIndex].dataset.price;
            const row = select.parentElement;
            row.querySelector('.price').value = price;
            calculateSubtotal(row.querySelector('input[name="quantities[]"]'));
        }

        function calculateSubtotal(input) {
            const row = input.parentElement;
            const qty = parseFloat(input.value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const subtotal = qty * price;
            row.querySelector('.subtotal').value = subtotal.toFixed(2);
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            const discountRate = {
                'Gold': 0.10,
                'Platinum': 0.15,
                'Silver': 0.05
            };

            const membership = document.getElementById('membership').value;
            const discount = total * (discountRate[membership] || 0);
            const final = total - discount;

            document.getElementById('total').value = total.toFixed(2);
            document.getElementById('discount').value = discount.toFixed(2);
            document.getElementById('final_total').value = final.toFixed(2);
        }
    </script>
</head>
<body>
    <h2 class="page-title">New Sale</h2>

    <form action="process_sale.php" method="POST" class="form-box sale-form">
        <!-- Customer Info -->
        <fieldset class="form-section customer-info">
            <legend class="section-title">Customer Info</legend>
            <input type="text" name="name" placeholder="Customer Name" required class="input-field">
            <input type="text" name="phone" placeholder="Phone Number" required class="input-field">

            <label class="label-field">Membership:
                <select name="membership" id="membership" onchange="calculateTotal()" class="select-field">
                    <option value="Silver">Silver</option>
                    <option value="Gold">Gold</option>
                    <option value="Platinum">Platinum</option>
                </select>
            </label>
        </fieldset>

        <!-- Product Selection -->
        <fieldset class="form-section product-section">
            <legend class="section-title">Products</legend>
            <div id="product-rows" class="product-rows"></div>
            <button type="button" onclick="addProductRow()" class="btn add-product-btn">Add Product</button>
        </fieldset>

        <!-- Totals -->
        <fieldset class="form-section total-section">
            <legend class="section-title">Total</legend>
            <label class="label-field">Total:
                <input type="text" id="total" name="total" readonly class="input-field readonly-field">
            </label><br>

            <label class="label-field">Discount:
                <input type="text" id="discount" name="discount" readonly class="input-field readonly-field">
            </label><br>

            <label class="label-field">Final Payable:
                <input type="text" id="final_total" name="final_total" readonly class="input-field readonly-field">
            </label>
        </fieldset>

        <button type="submit" class="btn submit-btn">Submit Sale</button>
    </form>

<<<<<<< Updated upstream
    <script>
        <?php
        // Pass PHP products array to JS
        echo "productData = {";
        foreach ($products as $p) {
            $name = addslashes($p['product_name']);
            $price = (float) $p['price'];
            echo "'{$p['id']}': {name: '{$name}', price: {$price}},";
=======

    <script>
        <?php
        // Output products to JavaScript
        echo "productData = {";
        while ($p = mysqli_fetch_assoc($products)) {
            echo "'{$p['id']}': {name: '" . addslashes($p['product_name']) . "', price: {$p['price']}},";
>>>>>>> Stashed changes
        }
        echo "};";
        ?>
    </script>
</body>
</html>
