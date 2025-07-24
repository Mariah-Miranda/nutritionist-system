<?php
// sales/new.php

// Ensure configuration and database connection are loaded
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php'; // Assuming auth.php contains requireLogin()

// Set page title for header
$pageTitle = 'New Sale Transaction';

// Include the header which contains navigation and top bar
require_once __DIR__ . '/../includes/header.php';

// Fetch default currency and tax rate from config
$defaultCurrency = DEFAULT_CURRENCY;
$taxRatePercent = TAX_RATE_PERCENT;

// Initialize variables for the form
$patients = [];
$products = [];

// Fetch initial list of patients (optional, for dropdown or initial display)
try {
    $stmt = $pdo->query("SELECT patient_id, full_name, membership_status FROM patients ORDER BY full_name ASC LIMIT 20");
    $patients = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching patients: " . $e->getMessage());
    // Handle error gracefully, e.g., show a message to the user
}

// Fetch initial list of products (optional, for dropdown or initial display)
try {
    $stmt = $pdo->query("SELECT id, product_name, price, stock FROM products WHERE stock > 0 ORDER BY product_name ASC LIMIT 20");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching products: " . $e->getMessage());
    // Handle error gracefully
}

?>

<div class="container mx-auto p-4 md:p-8">
    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">New Sale Transaction</h2>

        <!-- Customer Type Selection -->
        <div class="mb-6 border border-gray-200 rounded-lg p-4">
            <h3 class="text-xl font-medium text-gray-700 mb-4">Select Customer Type</h3>
            <div class="flex flex-wrap gap-4 mb-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="customer_type" value="Patient" class="form-radio h-5 w-5 text-green-600" checked id="customerTypePatient">
                    <span class="ml-2 text-gray-700 font-medium">
                        <i class="fas fa-user-injured mr-1 text-green-600"></i> Existing Patient
                    </span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="customer_type" value="Client" class="form-radio h-5 w-5 text-blue-600" id="customerTypeClient">
                    <span class="ml-2 text-gray-700 font-medium">
                        <i class="fas fa-user-tie mr-1 text-blue-600"></i> New Client
                    </span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="customer_type" value="Visitor" class="form-radio h-5 w-5 text-purple-600" id="customerTypeVisitor">
                    <span class="ml-2 text-gray-700 font-medium">
                        <i class="fas fa-user mr-1 text-purple-600"></i> Visitor
                    </span>
                </label>
            </div>

            <!-- Patient Selection (Initially Visible) -->
            <div id="patientSelectionArea" class="customer-type-area">
                <div class="relative flex-1 mb-4">
                    <input type="text" id="patientSearchInput" placeholder="Search patient by name or ID..."
                           class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition duration-150 ease-in-out"
                           autocomplete="off">
                    <div id="patientSearchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden">
                        <!-- Search results will be populated here -->
                    </div>
                </div>
                <div id="selectedPatientDisplay" class="text-gray-600 font-medium hidden">
                    Selected: <span id="selectedPatientName" class="font-semibold text-green-700"></span>
                    (<span id="selectedPatientMembership"></span>)
                    <input type="hidden" id="selectedPatientId" name="patient_id">
                </div>
            </div>

            <!-- New Client/Visitor Input (Initially Hidden) -->
            <div id="adhocClientInputArea" class="customer-type-area hidden">
                <div class="mb-4">
                    <label for="adhocClientName" class="block text-sm font-medium text-gray-700 mb-1">Client/Visitor Name:</label>
                    <input type="text" id="adhocClientName" placeholder="Enter name"
                           class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                </div>
                <div>
                    <label for="adhocClientPhone" class="block text-sm font-medium text-gray-700 mb-1">Client/Visitor Phone (Optional):</label>
                    <input type="text" id="adhocClientPhone" placeholder="Enter phone number"
                           class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                </div>
            </div>
        </div>


        <!-- Add Products -->
        <div class="mb-6 border border-gray-200 rounded-lg p-4">
            <h3 class="text-xl font-medium text-gray-700 mb-4">Add Products</h3>
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                <div class="relative flex-1">
                    <input type="text" id="productSearchInput" placeholder="Search products..."
                           class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition duration-150 ease-in-out"
                           autocomplete="off">
                    <div id="productSearchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden">
                        <!-- Search results will be populated here -->
                    </div>
                </div>
                <select id="productSelect" class="form-select w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition duration-150 ease-in-out">
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo htmlspecialchars($product['id']); ?>"
                                data-price="<?php echo htmlspecialchars($product['price']); ?>"
                                data-stock="<?php echo htmlspecialchars($product['stock']); ?>">
                            <?php echo htmlspecialchars($product['product_name']); ?> (Stock: <?php echo htmlspecialchars($product['stock']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="addProductBtn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg flex items-center justify-center transition duration-150 ease-in-out">
                    <i class="fas fa-plus mr-2"></i> Add
                </button>
            </div>
        </div>

        <!-- Sale Items Table -->
        <div class="mb-6 border border-gray-200 rounded-lg p-4 overflow-x-auto">
            <h3 class="text-xl font-medium text-gray-700 mb-4">Sale Items</h3>
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Product</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Price (<?php echo $defaultCurrency; ?>)</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Qty</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Discount (<?php echo $defaultCurrency; ?>)</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Subtotal (<?php echo $defaultCurrency; ?>)</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody id="saleItemsTableBody">
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-500">No items added yet</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary Totals -->
        <div class="mb-6 bg-gray-50 rounded-lg p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex justify-between items-center text-lg font-medium text-gray-700">
                <span>Subtotal:</span>
                <span id="displaySubtotal" class="font-semibold text-green-800"><?php echo $defaultCurrency; ?> 0.00</span>
            </div>
            <div class="flex justify-between items-center text-lg font-medium text-gray-700">
                <span>Member Discount:</span>
                <span id="displayMemberDiscount" class="font-semibold text-red-600"><?php echo $defaultCurrency; ?> 0.00</span>
            </div>
            <div class="flex justify-between items-center text-lg font-medium text-gray-700">
                <span>Tax (<?php echo htmlspecialchars($taxRatePercent); ?>%):</span>
                <span id="displayTax" class="font-semibold text-green-800"><?php echo DEFAULT_CURRENCY; ?> 0.00</span>
            </div>
            <div class="flex justify-between items-center text-xl font-bold text-gray-800 border-t-2 border-gray-300 pt-2 mt-2">
                <span>Total:</span>
                <span id="displayTotal" class="font-extrabold text-green-900"><?php echo DEFAULT_CURRENCY; ?> 0.00</span>
            </div>
        </div>

        <!-- Payment Method -->
        <div class="mb-6 border border-gray-200 rounded-lg p-4">
            <h3 class="text-xl font-medium text-gray-700 mb-4">Payment Method</h3>
            <div class="flex flex-wrap gap-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="payment_method" value="Cash" class="form-radio h-5 w-5 text-green-600" checked>
                    <span class="ml-2 text-gray-700 font-medium">
                        <i class="fas fa-money-bill-wave mr-1 text-green-600"></i> Cash
                    </span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="payment_method" value="Card" class="form-radio h-5 w-5 text-blue-600">
                    <span class="ml-2 text-gray-700 font-medium">
                        <i class="fas fa-credit-card mr-1 text-blue-600"></i> Card
                    </span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="payment_method" value="Digital" class="form-radio h-5 w-5 text-purple-600">
                    <span class="ml-2 text-gray-700 font-medium">
                        <i class="fas fa-mobile-alt mr-1 text-purple-600"></i> Digital
                    </span>
                </label>
            </div>
        </div>

        <!-- Action Button -->
        <div class="flex justify-end">
            <button type="button" id="completeSaleBtn" class="bg-green-700 hover:bg-green-800 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-150 ease-in-out text-lg">
                <i class="fas fa-print mr-2"></i> Print Sale
            </button>
        </div>
    </div>
</div>

<!-- Message Box Modal -->
<div id="messageBox" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full text-center">
        <h4 id="messageBoxTitle" class="text-xl font-semibold mb-4"></h4>
        <p id="messageBoxContent" class="text-gray-700 mb-6"></p>
        <button id="messageBoxCloseBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">OK</button>
    </div>
</div>

<script>
    // Global variables for sale management
    let saleItems = []; // Stores objects: {productId, productName, price, quantity, discount, subtotal, stockAvailable}
    let selectedPatient = null; // Stores {patient_id, full_name, membership_status}
    let selectedCustomerType = 'Patient'; // Default customer type

    // Constants from PHP
    const DEFAULT_CURRENCY = "<?php echo $defaultCurrency; ?>";
    const TAX_RATE_PERCENT = <?php echo $taxRatePercent; ?>; // 8

    // DOM Elements - Customer Type Selection
    const customerTypePatientRadio = document.getElementById('customerTypePatient');
    const customerTypeClientRadio = document.getElementById('customerTypeClient');
    const customerTypeVisitorRadio = document.getElementById('customerTypeVisitor');
    const patientSelectionArea = document.getElementById('patientSelectionArea');
    const adhocClientInputArea = document.getElementById('adhocClientInputArea');
    const adhocClientNameInput = document.getElementById('adhocClientName');
    const adhocClientPhoneInput = document.getElementById('adhocClientPhone');

    // DOM Elements - Patient Selection
    const patientSearchInput = document.getElementById('patientSearchInput');
    const patientSearchResults = document.getElementById('patientSearchResults');
    const selectedPatientDisplay = document.getElementById('selectedPatientDisplay');
    const selectedPatientName = document.getElementById('selectedPatientName');
    const selectedPatientMembership = document.getElementById('selectedPatientMembership');
    const selectedPatientIdInput = document.getElementById('selectedPatientId');

    // DOM Elements - Product Selection
    const productSearchInput = document.getElementById('productSearchInput');
    const productSearchResults = document.getElementById('productSearchResults');
    const productSelect = document.getElementById('productSelect');
    const addProductBtn = document.getElementById('addProductBtn');

    // DOM Elements - Sale Items & Totals
    const saleItemsTableBody = document.getElementById('saleItemsTableBody');
    const displaySubtotal = document.getElementById('displaySubtotal');
    const displayMemberDiscount = document.getElementById('displayMemberDiscount');
    const displayTax = document.getElementById('displayTax');
    const displayTotal = document.getElementById('displayTotal');
    const completeSaleBtn = document.getElementById('completeSaleBtn');

    // DOM Elements - Message Box
    const messageBox = document.getElementById('messageBox');
    const messageBoxTitle = document.getElementById('messageBoxTitle');
    const messageBoxContent = document.getElementById('messageBoxContent');
    const messageBoxCloseBtn = document.getElementById('messageBoxCloseBtn');

    /**
     * Shows a custom message box instead of alert().
     * @param {string} title - The title of the message box.
     * @param {string} message - The content message.
     */
    function showMessage(title, message) {
        messageBoxTitle.textContent = title;
        messageBoxContent.textContent = message;
        messageBox.classList.remove('hidden');
    }

    // Close message box
    messageBoxCloseBtn.addEventListener('click', () => {
        messageBox.classList.add('hidden');
    });

    /**
     * Handles customer type selection and updates UI visibility.
     */
    function handleCustomerTypeChange() {
        selectedCustomerType = document.querySelector('input[name="customer_type"]:checked').value;

        // Reset patient selection and ad-hoc inputs when type changes
        selectedPatient = null;
        patientSearchInput.value = '';
        selectedPatientDisplay.classList.add('hidden');
        selectedPatientIdInput.value = '';
        adhocClientNameInput.value = '';
        adhocClientPhoneInput.value = '';

        if (selectedCustomerType === 'Patient') {
            patientSelectionArea.classList.remove('hidden');
            adhocClientInputArea.classList.add('hidden');
        } else { // 'Client' or 'Visitor'
            patientSelectionArea.classList.add('hidden');
            adhocClientInputArea.classList.remove('hidden');
        }
        updateTotals(); // Recalculate totals as member discount might change
    }

    /**
     * Searches for patients based on input and displays results.
     */
    async function searchPatients() {
        const searchTerm = patientSearchInput.value.trim();
        if (searchTerm.length < 2) { // Require at least 2 characters for search
            patientSearchResults.classList.add('hidden');
            return;
        }

        try {
            // Path for search_patients.php is relative to new.php
            const response = await fetch(`search_patients.php?search_term=${encodeURIComponent(searchTerm)}`);
            const patients = await response.json();

            patientSearchResults.innerHTML = ''; // Clear previous results
            if (patients.length > 0) {
                patients.forEach(patient => {
                    const div = document.createElement('div');
                    div.className = 'p-2 cursor-pointer hover:bg-gray-100 rounded-md';
                    div.textContent = `${patient.full_name} (ID: ${patient.patient_unique_id})`;
                    div.dataset.patientId = patient.patient_id;
                    div.dataset.fullName = patient.full_name;
                    div.dataset.membershipStatus = patient.membership_status;
                    div.addEventListener('click', () => {
                        selectPatient(patient.patient_id, patient.full_name, patient.membership_status);
                        patientSearchResults.classList.add('hidden');
                    });
                    patientSearchResults.appendChild(div);
                });
                patientSearchResults.classList.remove('hidden');
            } else {
                patientSearchResults.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error searching patients:', error);
            showMessage('Error', 'Could not search patients. Please try again.');
        }
    }

    /**
     * Selects a patient and updates the display.
     * @param {number} id - Patient ID.
     * @param {string} name - Patient full name.
     * @param {string} membership - Patient membership status.
     */
    function selectPatient(id, name, membership) {
        selectedPatient = {
            patient_id: id,
            full_name: name,
            membership_status: membership
        };
        patientSearchInput.value = name; // Set input value to selected name
        selectedPatientName.textContent = name;
        selectedPatientMembership.textContent = membership;
        selectedPatientIdInput.value = id;
        selectedPatientDisplay.classList.remove('hidden');
        patientSearchResults.classList.add('hidden'); // Hide results after selection
        updateTotals(); // Recalculate totals for potential member discount
    }

    /**
     * Searches for products based on input and populates product select dropdown.
     */
    async function searchProducts() {
        const searchTerm = productSearchInput.value.trim();
        if (searchTerm.length < 2) {
            // If search term is too short, revert to initial product list or clear
            productSelect.innerHTML = '<option value="">Select Product</option>';
            return;
        }

        try {
            // Path for search_products.php is relative to new.php
            const response = await fetch(`search_products.php?search_term=${encodeURIComponent(searchTerm)}`);
            const products = await response.json();

            productSelect.innerHTML = '<option value="">Select Product</option>'; // Clear previous options
            if (products.length > 0) {
                products.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product.id;
                    option.dataset.price = product.price;
                    option.dataset.stock = product.stock;
                    option.textContent = `${product.product_name} (Stock: ${product.stock})`;
                    productSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error searching products:', error);
            showMessage('Error', 'Could not search products. Please try again.');
        }
    }

    /**
     * Adds a selected product to the sale items list.
     */
    function addProductToSale() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            showMessage('Selection Error', 'Please select a product to add.');
            return;
        }

        const productId = parseInt(selectedOption.value);
        const productName = selectedOption.textContent.split(' (Stock:')[0]; // Extract name without stock info
        const price = parseFloat(selectedOption.dataset.price);
        const stockAvailable = parseInt(selectedOption.dataset.stock);

        // Check if product already exists in saleItems
        const existingItemIndex = saleItems.findIndex(item => item.productId === productId);

        if (existingItemIndex !== -1) {
            // If exists, increment quantity
            if (saleItems[existingItemIndex].quantity < stockAvailable) {
                saleItems[existingItemIndex].quantity++;
            } else {
                showMessage('Stock Limit', `Cannot add more "${productName}". Maximum stock reached.`);
                return;
            }
        } else {
            // If new product, add to array
            if (stockAvailable > 0) {
                saleItems.push({
                    productId: productId,
                    productName: productName,
                    price: price,
                    quantity: 1,
                    discount: 0, // Default discount
                    subtotal: price, // Initial subtotal
                    stockAvailable: stockAvailable // Keep track of available stock
                });
            } else {
                showMessage('Out of Stock', `"${productName}" is out of stock.`);
                return;
            }
        }
        renderSaleItems();
        updateTotals();
    }

    /**
     * Renders the sale items in the table body.
     */
    function renderSaleItems() {
        saleItemsTableBody.innerHTML = ''; // Clear existing rows

        if (saleItems.length === 0) {
            saleItemsTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-500">No items added yet</td></tr>`;
            return;
        }

        saleItems.forEach((item, index) => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-200 last:border-b-0';
            row.innerHTML = `
                <td class="py-3 px-4 text-gray-800">${item.productName}</td>
                <td class="py-3 px-4 text-gray-800">${DEFAULT_CURRENCY} ${item.price.toFixed(2)}</td>
                <td class="py-3 px-4">
                    <input type="number" value="${item.quantity}" min="1" max="${item.stockAvailable}"
                           class="w-20 px-2 py-1 border border-gray-300 rounded-md text-center quantity-input"
                           data-index="${index}">
                </td>
                <td class="py-3 px-4">
                    <input type="number" value="${item.discount.toFixed(2)}" min="0"
                           class="w-24 px-2 py-1 border border-gray-300 rounded-md text-center discount-input"
                           data-index="${index}">
                </td>
                <td class="py-3 px-4 text-gray-800 font-medium">${DEFAULT_CURRENCY} ${item.subtotal.toFixed(2)}</td>
                <td class="py-3 px-4">
                    <button type="button" class="text-red-600 hover:text-red-800 remove-item-btn" data-index="${index}">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                </td>
            `;
            saleItemsTableBody.appendChild(row);
        });

        // Add event listeners to newly rendered inputs and buttons
        saleItemsTableBody.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', (e) => handleQtyChange(e.target.dataset.index, parseInt(e.target.value)));
        });
        saleItemsTableBody.querySelectorAll('.discount-input').forEach(input => {
            input.addEventListener('change', (e) => handleDiscountChange(e.target.dataset.index, parseFloat(e.target.value)));
        });
        saleItemsTableBody.querySelectorAll('.remove-item-btn').forEach(button => {
            button.addEventListener('click', (e) => removeItem(e.target.dataset.index || e.target.closest('button').dataset.index));
        });
    }

    /**
     * Updates the quantity of a sale item.
     * @param {number} index - Index of the item in saleItems array.
     * @param {number} newQty - New quantity.
     */
    function handleQtyChange(index, newQty) {
        index = parseInt(index);
        newQty = parseInt(newQty);

        if (isNaN(newQty) || newQty < 1) {
            showMessage('Invalid Quantity', 'Quantity must be at least 1.');
            renderSaleItems(); // Re-render to revert invalid input
            return;
        }
        if (newQty > saleItems[index].stockAvailable) {
            showMessage('Stock Limit', `Only ${saleItems[index].stockAvailable} units of "${saleItems[index].productName}" available.`);
            renderSaleItems(); // Re-render to revert invalid input
            return;
        }

        saleItems[index].quantity = newQty;
        saleItems[index].subtotal = (saleItems[index].price * saleItems[index].quantity) - saleItems[index].discount;
        renderSaleItems();
        updateTotals();
    }

    /**
     * Updates the discount of a sale item.
     * @param {number} index - Index of the item in saleItems array.
     * @param {number} newDiscount - New discount amount.
     */
    function handleDiscountChange(index, newDiscount) {
        index = parseInt(index);
        newDiscount = parseFloat(newDiscount);

        if (isNaN(newDiscount) || newDiscount < 0) {
            showMessage('Invalid Discount', 'Discount cannot be negative.');
            renderSaleItems(); // Re-render to revert invalid input
            return;
        }
        if (newDiscount > (saleItems[index].price * saleItems[index].quantity)) {
            showMessage('Invalid Discount', 'Discount cannot exceed item subtotal.');
            renderSaleItems(); // Re-render to revert invalid input
            return;
        }

        saleItems[index].discount = newDiscount;
        saleItems[index].subtotal = (saleItems[index].price * saleItems[index].quantity) - saleItems[index].discount;
        renderSaleItems();
        updateTotals();
    }

    /**
     * Removes an item from the sale items list.
     * @param {number} index - Index of the item to remove.
     */
    function removeItem(index) {
        saleItems.splice(index, 1);
        renderSaleItems();
        updateTotals();
    }

    /**
     * Calculates and updates the total summary figures.
     */
    function updateTotals() {
        let currentSubtotal = saleItems.reduce((sum, item) => sum + item.subtotal, 0);
        let memberDiscountAmount = 0;

        // Apply member discount ONLY if customer type is 'Patient' and a patient is selected
        if (selectedCustomerType === 'Patient' && selectedPatient && selectedPatient.membership_status) {
            if (selectedPatient.membership_status === 'Standard') {
                memberDiscountAmount = currentSubtotal * 0.05; // 5% discount
            } else if (selectedPatient.membership_status === 'Premium') {
                memberDiscountAmount = currentSubtotal * 0.10; // 10% discount
            }
        }

        currentSubtotal -= memberDiscountAmount; // Apply member discount before tax

        const taxAmount = currentSubtotal * (TAX_RATE_PERCENT / 100);
        const totalAmount = currentSubtotal + taxAmount;

        displaySubtotal.textContent = `${DEFAULT_CURRENCY} ${currentSubtotal.toFixed(2)}`;
        displayMemberDiscount.textContent = `${DEFAULT_CURRENCY} ${memberDiscountAmount.toFixed(2)}`;
        displayTax.textContent = `${DEFAULT_CURRENCY} ${taxAmount.toFixed(2)}`;
        displayTotal.textContent = `${DEFAULT_CURRENCY} ${totalAmount.toFixed(2)}`;
    }

    /**
     * Handles the completion of the sale transaction.
     */
    async function completeSale() {
        let patientIdToSend = null;
        let customerNameToSend = null;
        let customerPhoneToSend = null;

        if (selectedCustomerType === 'Patient') {
            if (!selectedPatient) {
                showMessage('Missing Patient', 'Please select a patient before completing the sale.');
                return;
            }
            patientIdToSend = selectedPatient.patient_id;
        } else { // 'Client' or 'Visitor'
            customerNameToSend = adhocClientNameInput.value.trim();
            customerPhoneToSend = adhocClientPhoneInput.value.trim();

            if (selectedCustomerType === 'Client' && customerNameToSend === '') {
                showMessage('Missing Client Name', 'Please enter a name for the new client.');
                return;
            }
            // Visitor can have an empty name
        }

        if (saleItems.length === 0) {
            showMessage('No Items', 'Please add products to the sale before completing.');
            return;
        }

        const totalAmount = parseFloat(displayTotal.textContent.replace(DEFAULT_CURRENCY + ' ', ''));
        const memberDiscountPercent = (selectedCustomerType === 'Patient' && selectedPatient && selectedPatient.membership_status === 'Standard') ? 5 :
                                      ((selectedCustomerType === 'Patient' && selectedPatient && selectedPatient.membership_status === 'Premium') ? 10 : 0);
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

        // Prepare sale items for submission
        const itemsForSubmission = saleItems.map(item => ({
            product_id: item.productId,
            quantity: item.quantity,
            price: item.price, // Original price per unit
            subtotal: item.subtotal, // Subtotal after item-specific discount
            // Note: Item-specific discount is already factored into subtotal
        }));

        const formData = new FormData();
        formData.append('customer_type', selectedCustomerType);
        if (patientIdToSend !== null) {
            formData.append('patient_id', patientIdToSend);
        }
        if (customerNameToSend !== null) {
            formData.append('customer_name', customerNameToSend);
        }
        if (customerPhoneToSend !== null) {
            formData.append('customer_phone', customerPhoneToSend);
        }

        formData.append('total_amount', totalAmount);
        formData.append('discount_percent', memberDiscountPercent); // This is the *member* discount %
        formData.append('payment_method', paymentMethod);
        formData.append('sale_items', JSON.stringify(itemsForSubmission)); // Send items as JSON string

        try {
            const response = await fetch('process_sale.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showMessage('Sale Completed!', result.message);
                // Optionally redirect or clear form
                setTimeout(() => {
                    window.location.href = `receipt.php?sale_id=${result.sale_id}`;
                }, 1500);
            } else {
                showMessage('Sale Failed', result.message);
            }
        } catch (error) {
            console.error('Error completing sale:', error);
            showMessage('Error', 'An unexpected error occurred. Please try again.');
        }
    }

    // Event Listeners
    customerTypePatientRadio.addEventListener('change', handleCustomerTypeChange);
    customerTypeClientRadio.addEventListener('change', handleCustomerTypeChange);
    customerTypeVisitorRadio.addEventListener('change', handleCustomerTypeChange);

<<<<<<< Updated upstream
    patientSearchInput.addEventListener('keyup', searchPatients);
    addProductBtn.addEventListener('click', addProductToSale);
    completeSaleBtn.addEventListener('click', completeSale);

    // Hide search results when clicking outside
    document.addEventListener('click', (event) => {
        if (!patientSearchInput.contains(event.target) && !patientSearchResults.contains(event.target)) {
            patientSearchResults.classList.add('hidden');
=======
            <label class="label-field">Membership:
                <select name="membership" id="membership" onchange="calculateTotal()" class="select-field">
                    <option value="No membership">No membership</option>
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
            <button type="button" onclick="addProductRow()" class="btn add-product-btn">+Add Product</button>
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

    <script>
        <?php
        // Pass PHP products array to JS
        echo "productData = {";
        foreach ($products as $p) {
            $name = addslashes($p['product_name']);
            $price = (float) $p['price'];
            echo "'{$p['id']}': {name: '{$name}', price: {$price}},";
>>>>>>> Stashed changes
        }
    });

    // Initial render and total calculation
    document.addEventListener('DOMContentLoaded', () => {
        renderSaleItems();
        updateTotals();
        handleCustomerTypeChange(); // Initialize UI based on default selected type
    });

</script>

<?php
// Include the footer
require_once __DIR__ . '/../includes/footer.php'; // Assuming you have a footer.php
?>
