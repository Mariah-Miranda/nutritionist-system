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

?>

<div class="container mx-auto p-4 lg:p-8">
    <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 lg:p-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">New Sale</h2>

        <!-- Customer Type Selection -->
        <div class="mb-6 border border-gray-200 rounded-lg p-4">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">1. Select Customer</h3>
            <div class="flex flex-wrap gap-4 mb-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="customer_type" value="Patient" class="form-radio h-5 w-5 text-green-600" checked id="customerTypePatient">
                    <span class="ml-2 text-gray-700 font-medium">
                        <i class="fas fa-user-injured mr-1 text-green-600"></i> Existing Patient
                    </span>
                </label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="customer_type" value="Client" class="form-radio h-5 w-5 text-blue-600" id="customerTypeClient">
                    <span class="ml-2 text-gray-700 font-medium">
                        <i class="fas fa-user-tie mr-1 text-blue-600"></i> New Client
                    </span>
                </label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="customer_type" value="Visitor" class="form-radio h-5 w-5 text-purple-600" id="customerTypeVisitor">
                    <span class="ml-2 text-gray-700 font-medium">
                        <i class="fas fa-user mr-1 text-purple-600"></i> Visitor
                    </span>
                </label>
            </div>

            <!-- Patient Selection -->
            <div id="patientSelectionArea" class="customer-type-area">
                <div class="relative flex-1 mb-4">
                    <input type="text" id="patientSearchInput" placeholder="Search patient by name or ID..."
                           class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition duration-150 ease-in-out"
                           autocomplete="off">
                    <div id="patientSearchResults" class="absolute z-20 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden"></div>
                </div>
                <div id="selectedPatientDisplay" class="text-gray-600 font-medium hidden p-2 bg-green-50 rounded-lg">
                    Selected: <span id="selectedPatientName" class="font-semibold text-green-700"></span>
                    (<span id="selectedPatientMembership"></span>)
                    <input type="hidden" id="selectedPatientId" name="patient_id">
                </div>
            </div>

            <!-- New Client/Visitor Input -->
            <div id="adhocClientInputArea" class="customer-type-area hidden space-y-4">
                <div>
                    <label for="adhocClientName" class="block text-sm font-medium text-gray-700 mb-1">Client/Visitor Name:</label>
                    <input type="text" id="adhocClientName" placeholder="Enter name"
                           class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition">
                </div>
                <div>
                    <label for="adhocClientPhone" class="block text-sm font-medium text-gray-700 mb-1">Client/Visitor Phone (Optional):</label>
                    <input type="text" id="adhocClientPhone" placeholder="Enter phone number"
                           class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition">
                </div>
            </div>
        </div>


        <!-- Add Products -->
        <div class="mb-6 border border-gray-200 rounded-lg p-4">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">2. Add Products</h3>
            <div class="flex flex-col sm:flex-row gap-4 mb-2">
                <div class="relative flex-1">
                    <input type="text" id="productSearchInput" placeholder="Search products by name..."
                           class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition"
                           autocomplete="off">
                    <div id="productSearchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden"></div>
                </div>
                <button type="button" id="addProductBtn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg flex items-center justify-center transition duration-150 ease-in-out">
                    <i class="fas fa-plus mr-2"></i> Add to Cart
                </button>
            </div>
        </div>

        <!-- Sale Items Table -->
        <div class="mb-6 border border-gray-200 rounded-lg p-4">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">3. Review Cart</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-2 sm:px-4 text-left text-sm font-semibold text-gray-600">Product</th>
                            <th class="py-3 px-2 sm:px-4 text-left text-sm font-semibold text-gray-600">Price</th>
                            <th class="py-3 px-2 sm:px-4 text-left text-sm font-semibold text-gray-600">Qty</th>
                            <th class="py-3 px-2 sm:px-4 text-left text-sm font-semibold text-gray-600">Discount</th>
                            <th class="py-3 px-2 sm:px-4 text-left text-sm font-semibold text-gray-600">Subtotal</th>
                            <th class="py-3 px-2 sm:px-4 text-left text-sm font-semibold text-gray-600">Action</th>
                        </tr>
                    </thead>
                    <tbody id="saleItemsTableBody">
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">No items added yet</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary & Payment -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Summary Totals -->
            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                 <div class="flex justify-between items-center text-lg font-medium text-gray-700">
                    <span>Subtotal:</span>
                    <span id="displaySubtotal" class="font-semibold text-gray-800"><?php echo $defaultCurrency; ?> 0.00</span>
                </div>
                <div class="flex justify-between items-center text-lg font-medium text-gray-700">
                    <span>Member Discount:</span>
                    <span id="displayMemberDiscount" class="font-semibold text-red-600">- <?php echo DEFAULT_CURRENCY; ?> 0.00</span>
                </div>
                <div class="flex justify-between items-center text-lg font-medium text-gray-700">
                    <span>Tax (<?php echo htmlspecialchars($taxRatePercent); ?>%):</span>
                    <span id="displayTax" class="font-semibold text-gray-800">+ <?php echo DEFAULT_CURRENCY; ?> 0.00</span>
                </div>
                <div class="flex justify-between items-center text-2xl font-bold text-gray-800 border-t-2 border-gray-300 pt-3 mt-3">
                    <span>Total:</span>
                    <span id="displayTotal" class="font-extrabold text-green-700"><?php echo DEFAULT_CURRENCY; ?> 0.00</span>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">4. Payment Method</h3>
                <div class="flex flex-wrap gap-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="payment_method" value="Cash" class="form-radio h-5 w-5 text-green-600" checked>
                        <span class="ml-2 text-gray-700 font-medium"><i class="fas fa-money-bill-wave mr-1 text-green-600"></i> Cash</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="payment_method" value="Card" class="form-radio h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700 font-medium"><i class="fas fa-credit-card mr-1 text-blue-600"></i> Card</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="payment_method" value="Digital" class="form-radio h-5 w-5 text-purple-600">
                        <span class="ml-2 text-gray-700 font-medium"><i class="fas fa-mobile-alt mr-1 text-purple-600"></i> Digital</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <div class="flex justify-end mt-8">
            <button type="button" id="completeSaleBtn" class="w-full sm:w-auto bg-green-700 hover:bg-green-800 text-white font-bold py-3 px-8 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out text-lg">
                <i class="fas fa-check-circle mr-2"></i> Complete Sale
            </button>
        </div>
    </div>
</div>

<!-- Message Box Modal -->
<div id="messageBox" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full text-center">
        <h4 id="messageBoxTitle" class="text-xl font-semibold mb-4"></h4>
        <p id="messageBoxContent" class="text-gray-700 mb-6"></p>
        <button id="messageBoxCloseBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">OK</button>
    </div>
</div>

<script>
    // --- START OF SALE MANAGEMENT SCRIPT ---

    // Global state variables
    let saleItems = [];
    let selectedPatient = null;
    let selectedCustomerType = 'Patient';
    let selectedProductFromSearch = null;

    // Constants from PHP
    const DEFAULT_CURRENCY = "<?php echo $defaultCurrency; ?>";
    const TAX_RATE_PERCENT = <?php echo $taxRatePercent; ?>;

    // DOM Element references
    const customerTypeRadios = document.querySelectorAll('input[name="customer_type"]');
    const patientSelectionArea = document.getElementById('patientSelectionArea');
    const adhocClientInputArea = document.getElementById('adhocClientInputArea');
    const adhocClientNameInput = document.getElementById('adhocClientName');
    const adhocClientPhoneInput = document.getElementById('adhocClientPhone');
    const patientSearchInput = document.getElementById('patientSearchInput');
    const patientSearchResults = document.getElementById('patientSearchResults');
    const selectedPatientDisplay = document.getElementById('selectedPatientDisplay');
    const selectedPatientName = document.getElementById('selectedPatientName');
    const selectedPatientMembership = document.getElementById('selectedPatientMembership');
    const selectedPatientIdInput = document.getElementById('selectedPatientId');
    const productSearchInput = document.getElementById('productSearchInput');
    const productSearchResults = document.getElementById('productSearchResults');
    const addProductBtn = document.getElementById('addProductBtn');
    const saleItemsTableBody = document.getElementById('saleItemsTableBody');
    const displaySubtotal = document.getElementById('displaySubtotal');
    const displayMemberDiscount = document.getElementById('displayMemberDiscount');
    const displayTax = document.getElementById('displayTax');
    const displayTotal = document.getElementById('displayTotal');
    const completeSaleBtn = document.getElementById('completeSaleBtn');
    const messageBox = document.getElementById('messageBox');
    const messageBoxTitle = document.getElementById('messageBoxTitle');
    const messageBoxContent = document.getElementById('messageBoxContent');
    const messageBoxCloseBtn = document.getElementById('messageBoxCloseBtn');

    /**
     * Shows a custom message box.
     */
    function showMessage(title, message) {
        messageBoxTitle.textContent = title;
        messageBoxContent.textContent = message;
        messageBox.classList.remove('hidden');
    }

    /**
     * Handles customer type selection UI changes.
     */
    function handleCustomerTypeChange() {
        selectedCustomerType = document.querySelector('input[name="customer_type"]:checked').value;
        selectedPatient = null;
        patientSearchInput.value = '';
        selectedPatientDisplay.classList.add('hidden');
        selectedPatientIdInput.value = '';
        adhocClientNameInput.value = '';
        adhocClientPhoneInput.value = '';

        patientSelectionArea.classList.toggle('hidden', selectedCustomerType !== 'Patient');
        adhocClientInputArea.classList.toggle('hidden', selectedCustomerType === 'Patient');
        updateTotals();
    }

    /**
     * Searches for patients via fetch API.
     */
    async function searchPatients() {
        const searchTerm = patientSearchInput.value.trim();
        if (searchTerm.length < 2) {
            patientSearchResults.classList.add('hidden');
            return;
        }

        try {
            const response = await fetch(`search_patients.php?search_term=${encodeURIComponent(searchTerm)}`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const patients = await response.json();

            patientSearchResults.innerHTML = '';
            if (patients.length > 0) {
                patients.forEach(patient => {
                    const div = document.createElement('div');
                    div.className = 'p-3 cursor-pointer hover:bg-gray-100 rounded-md';
                    div.textContent = `${patient.full_name} (ID: ${patient.patient_unique_id})`;
                    div.addEventListener('click', () => selectPatient(patient));
                    patientSearchResults.appendChild(div);
                });
                patientSearchResults.classList.remove('hidden');
            } else {
                patientSearchResults.innerHTML = '<div class="p-3 text-gray-500">No patients found.</div>';
                patientSearchResults.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error searching patients:', error);
            showMessage('Error', 'Could not search for patients. Please check the connection and try again.');
        }
    }

    /**
     * Sets the selected patient and updates UI.
     */
    function selectPatient(patient) {
        selectedPatient = patient;
        patientSearchInput.value = patient.full_name;
        selectedPatientName.textContent = patient.full_name;
        selectedPatientMembership.textContent = patient.membership_status;
        selectedPatientIdInput.value = patient.patient_id;
        selectedPatientDisplay.classList.remove('hidden');
        patientSearchResults.classList.add('hidden');
        updateTotals();
    }

    /**
     * Searches for products via fetch API.
     */
    async function searchProducts() {
        const searchTerm = productSearchInput.value.trim();
        if (searchTerm.length < 2) {
            productSearchResults.classList.add('hidden');
            selectedProductFromSearch = null;
            return;
        }

        try {
            const response = await fetch(`search_products.php?search_term=${encodeURIComponent(searchTerm)}`);
            if (!response.ok) {
                // If response is not ok, try to read as text to see PHP error
                const errorText = await response.text();
                throw new Error(`Server error: ${response.status}. Response: ${errorText}`);
            }
            const products = await response.json();

            productSearchResults.innerHTML = '';
            if (products.length > 0) {
                products.forEach(product => {
                    const div = document.createElement('div');
                    div.className = 'p-3 cursor-pointer hover:bg-gray-100 rounded-md';
                    div.innerHTML = `
                        <div class="font-semibold">${product.product_name}</div>
                        <div class="text-sm text-gray-600">Stock: ${product.stock} | Price: ${DEFAULT_CURRENCY} ${parseFloat(product.price).toFixed(2)}</div>
                    `;
                    div.addEventListener('click', () => {
                        selectedProductFromSearch = {
                            productId: parseInt(product.id),
                            productName: product.product_name,
                            price: parseFloat(product.price),
                            stockAvailable: parseInt(product.stock)
                        };
                        productSearchInput.value = product.product_name;
                        productSearchResults.classList.add('hidden');
                    });
                    productSearchResults.appendChild(div);
                });
                productSearchResults.classList.remove('hidden');
            } else {
                productSearchResults.innerHTML = '<div class="p-3 text-gray-500">No products found.</div>';
                productSearchResults.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error searching products:', error);
            showMessage('Product Search Failed', 'Could not load products. There might be a server issue. Please contact support if this persists.');
            productSearchResults.innerHTML = `<div class="p-3 text-red-500">Error loading products.</div>`;
            productSearchResults.classList.remove('hidden');
            selectedProductFromSearch = null;
        }
    }

    /**
     * Adds a selected product to the sale cart.
     */
    function addProductToSale() {
        if (!selectedProductFromSearch) {
            showMessage('Selection Error', 'Please search for a product and select one from the list first.');
            return;
        }

        const { productId, productName, price, stockAvailable } = selectedProductFromSearch;
        const existingItem = saleItems.find(item => item.productId === productId);

        if (existingItem) {
            if (existingItem.quantity < stockAvailable) {
                existingItem.quantity++;
            } else {
                showMessage('Stock Limit', `Cannot add more "${productName}". Maximum stock reached.`);
                return;
            }
        } else {
            if (stockAvailable > 0) {
                saleItems.push({ productId, productName, price, quantity: 1, discount: 0, stockAvailable });
            } else {
                showMessage('Out of Stock', `"${productName}" is out of stock.`);
                return;
            }
        }
        
        // Clear search and selection
        productSearchInput.value = '';
        selectedProductFromSearch = null;
        
        renderSaleItems();
        updateTotals();
    }

    /**
     * Renders the sale items in the cart table.
     */
    function renderSaleItems() {
        saleItemsTableBody.innerHTML = '';

        if (saleItems.length === 0) {
            saleItemsTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-500">No items added yet</td></tr>`;
            return;
        }

        saleItems.forEach((item, index) => {
            const itemSubtotal = (item.price * item.quantity) - item.discount;
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-200 last:border-b-0';
            row.innerHTML = `
                <td class="py-3 px-2 sm:px-4 text-gray-800">${item.productName}</td>
                <td class="py-3 px-2 sm:px-4 text-gray-800">${item.price.toFixed(2)}</td>
                <td class="py-3 px-2 sm:px-4">
                    <input type="number" value="${item.quantity}" min="1" max="${item.stockAvailable}"
                           class="w-16 px-2 py-1 border border-gray-300 rounded-md text-center quantity-input"
                           data-index="${index}">
                </td>
                <td class="py-3 px-2 sm:px-4">
                    <input type="number" value="${item.discount.toFixed(2)}" min="0" step="0.01"
                           class="w-20 px-2 py-1 border border-gray-300 rounded-md text-center discount-input"
                           data-index="${index}">
                </td>
                <td class="py-3 px-2 sm:px-4 text-gray-800 font-medium">${itemSubtotal.toFixed(2)}</td>
                <td class="py-3 px-2 sm:px-4">
                    <button type="button" class="text-red-600 hover:text-red-800 remove-item-btn" data-index="${index}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
            saleItemsTableBody.appendChild(row);
        });

        // Re-add event listeners
        saleItemsTableBody.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', e => handleQtyChange(e.target.dataset.index, parseInt(e.target.value)));
        });
        saleItemsTableBody.querySelectorAll('.discount-input').forEach(input => {
            input.addEventListener('change', e => handleDiscountChange(e.target.dataset.index, parseFloat(e.target.value)));
        });
        saleItemsTableBody.querySelectorAll('.remove-item-btn').forEach(button => {
            button.addEventListener('click', e => removeItem(e.target.closest('button').dataset.index));
        });
    }

    function handleQtyChange(index, newQty) {
        const item = saleItems[index];
        if (isNaN(newQty) || newQty < 1) {
            showMessage('Invalid Quantity', 'Quantity must be at least 1.');
        } else if (newQty > item.stockAvailable) {
            showMessage('Stock Limit', `Only ${item.stockAvailable} units of "${item.productName}" are available.`);
        } else {
            item.quantity = newQty;
        }
        renderSaleItems();
        updateTotals();
    }

    function handleDiscountChange(index, newDiscount) {
        const item = saleItems[index];
        const maxDiscount = item.price * item.quantity;
        if (isNaN(newDiscount) || newDiscount < 0) {
            showMessage('Invalid Discount', 'Discount cannot be negative.');
        } else if (newDiscount > maxDiscount) {
            showMessage('Invalid Discount', `Discount cannot exceed the item subtotal of ${maxDiscount.toFixed(2)}.`);
        } else {
            item.discount = newDiscount;
        }
        renderSaleItems();
        updateTotals();
    }

    function removeItem(index) {
        saleItems.splice(index, 1);
        renderSaleItems();
        updateTotals();
    }

    /**
     * Calculates and updates all summary totals.
     */
    function updateTotals() {
        let subtotal = saleItems.reduce((sum, item) => sum + (item.price * item.quantity) - item.discount, 0);
        let memberDiscountAmount = 0;

        if (selectedCustomerType === 'Patient' && selectedPatient && selectedPatient.membership_status) {
            const discountRate = selectedPatient.membership_status === 'Premium' ? 0.10 : (selectedPatient.membership_status === 'Standard' ? 0.05 : 0);
            memberDiscountAmount = subtotal * discountRate;
        }

        const subtotalAfterMemberDiscount = subtotal - memberDiscountAmount;
        const taxAmount = subtotalAfterMemberDiscount * (TAX_RATE_PERCENT / 100);
        const totalAmount = subtotalAfterMemberDiscount + taxAmount;

        displaySubtotal.textContent = `${DEFAULT_CURRENCY} ${subtotal.toFixed(2)}`;
        displayMemberDiscount.textContent = `- ${DEFAULT_CURRENCY} ${memberDiscountAmount.toFixed(2)}`;
        displayTax.textContent = `+ ${DEFAULT_CURRENCY} ${taxAmount.toFixed(2)}`;
        displayTotal.textContent = `${DEFAULT_CURRENCY} ${totalAmount.toFixed(2)}`;
    }

    /**
     * Gathers all data and processes the sale.
     */
    async function completeSale() {
        if (saleItems.length === 0) {
            showMessage('Empty Cart', 'Please add products to the sale before completing.');
            return;
        }
        
        let patientIdToSend = null;
        let customerNameToSend = null;

        if (selectedCustomerType === 'Patient') {
            if (!selectedPatient) {
                showMessage('Missing Patient', 'Please select a patient for this sale.');
                return;
            }
            patientIdToSend = selectedPatient.patient_id;
        } else {
            customerNameToSend = adhocClientNameInput.value.trim();
            if (selectedCustomerType === 'Client' && !customerNameToSend) {
                showMessage('Missing Client Name', 'Please enter a name for the new client.');
                return;
            }
        }

        const totalAmount = parseFloat(displayTotal.textContent.replace(/[^\d.-]/g, ''));
        
        // Correctly calculate member discount percentage for submission
        let memberDiscountRate = 0;
        if (selectedCustomerType === 'Patient' && selectedPatient && selectedPatient.membership_status) {
            if (selectedPatient.membership_status === 'Premium') {
                memberDiscountRate = 10;
            } else if (selectedPatient.membership_status === 'Standard') {
                memberDiscountRate = 5;
            }
        }

        const formData = new FormData();
        formData.append('customer_type', selectedCustomerType);
        formData.append('patient_id', patientIdToSend || '');
        formData.append('customer_name', customerNameToSend || '');
        formData.append('customer_phone', adhocClientPhoneInput.value.trim() || '');
        formData.append('total_amount', totalAmount);
        formData.append('discount_percent', memberDiscountRate);
        formData.append('payment_method', document.querySelector('input[name="payment_method"]:checked').value);
        formData.append('sale_items', JSON.stringify(saleItems.map(item => ({
            product_id: item.productId,
            quantity: item.quantity,
            price: item.price,
            subtotal: (item.price * item.quantity) - item.discount
        }))));

        try {
            const response = await fetch('process_sale.php', { method: 'POST', body: formData });
            
            // Try to parse the response as JSON. If it fails, get the text for debugging.
            const responseText = await response.text();
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                // If JSON parsing fails, we have a server-side error (likely PHP).
                console.error("Failed to parse JSON response from server.");
                console.error("Server response:", responseText);
                throw new Error("Server returned a non-JSON response. Check console for details.");
            }

            if (result.success && result.sale_id) {
                showMessage('Success!', 'Sale completed successfully. The receipt will open in a new tab.');
                setTimeout(() => {
                    window.open(`receipt.php?sale_id=${result.sale_id}`, '_blank');
                    resetForm();
                }, 1500);
            } else {
                // Handle business logic errors returned from the server
                showMessage('Sale Failed', result.message || 'An unknown error occurred on the server.');
                console.error('Server-side sale processing error:', result.message);
            }
        } catch (error) {
            // Handle network errors or the non-JSON response error thrown above
            console.error('Error completing sale:', error);
            showMessage('Error', 'A critical error occurred. Please check the browser console and contact support.');
        }
    }
    
    /**
     * Resets the entire form to its initial state.
     */
    function resetForm() {
        saleItems = [];
        selectedPatient = null;
        selectedProductFromSearch = null;
        // Manually clear inputs since there's no <form> element
        patientSearchInput.value = '';
        productSearchInput.value = '';
        adhocClientNameInput.value = '';
        adhocClientPhoneInput.value = '';
        selectedPatientDisplay.classList.add('hidden');
        customerTypeRadios[0].checked = true; // Reset to 'Patient'
        handleCustomerTypeChange(); // Update UI based on reset
        renderSaleItems();
        updateTotals();
    }


    // --- EVENT LISTENERS ---
    document.addEventListener('DOMContentLoaded', () => {
        renderSaleItems();
        updateTotals();
        handleCustomerTypeChange();
    });

    customerTypeRadios.forEach(radio => radio.addEventListener('change', handleCustomerTypeChange));
    patientSearchInput.addEventListener('input', searchPatients);
    productSearchInput.addEventListener('input', searchProducts);
    addProductBtn.addEventListener('click', addProductToSale);
    completeSaleBtn.addEventListener('click', completeSale);
    messageBoxCloseBtn.addEventListener('click', () => messageBox.classList.add('hidden'));

    // Hide search results when clicking outside
    document.addEventListener('click', (event) => {
        if (!patientSearchInput.contains(event.target) && !patientSearchResults.contains(event.target)) {
            patientSearchResults.classList.add('hidden');
        }
        if (!productSearchInput.contains(event.target) && !productSearchResults.contains(event.target)) {
            productSearchResults.classList.add('hidden');
        }
    });

    // --- END OF SALE MANAGEMENT SCRIPT ---
</script>

<?php
// Include the footer
require_once __DIR__ . '/../includes/footer.php';
?>
