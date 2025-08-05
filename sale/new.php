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
    <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 lg:p-12">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-4">New Sale</h2>

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
                <input type="hidden" id="selectedPatientId">
                <div id="selectedPatientDisplay" class="mt-4 p-4 bg-blue-100 border border-blue-300 rounded-lg shadow-sm hidden transition-all duration-300 ease-in-out">
                    <p class="font-semibold text-blue-800">Patient Selected:</p>
                    <p class="text-blue-700"><span id="displayPatientName" class="font-bold"></span> (<span id="displayPatientId"></span>)</p>
                </div>
            </div>
        </div>

        <!-- Product Selection Section -->
        <div class="mb-8 p-6 bg-gray-50 rounded-xl shadow-inner">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">2. Add Products to Cart</h3>
            <div class="relative mb-4">
                <label for="productSearchInput" class="block text-sm font-medium text-gray-700 mb-2">Search by Product Name</label>
                <input type="text" id="productSearchInput" placeholder="e.g., Vitamin C" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                <div id="productSearchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 max-h-60 overflow-y-auto shadow-lg hidden">
                    <!-- Product search results -->
                </div>
            </div>

            <button id="addProductBtn" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105">
                Add Selected Product
            </button>
        </div>

        <!-- Sale Items List and Summary -->
        <div class="mb-8 p-6 bg-gray-50 rounded-xl shadow-inner">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">3. Cart & Summary</h3>
            <div id="saleItemsList" class="space-y-4">
                <!-- Sale items will be rendered here -->
            </div>
            <div id="emptyCartMessage" class="text-center text-gray-500 py-8 transition-opacity duration-300">
                Your cart is empty. Add some products to start a sale.
            </div>

            <div id="saleSummary" class="mt-6 pt-4 border-t-2 border-gray-200 space-y-2">
                <div class="flex justify-between text-lg font-medium text-gray-700">
                    <span>Subtotal:</span>
                    <span><span id="subtotalAmount">0.00</span> <?php echo $defaultCurrency; ?></span>
                </div>
                <div class="flex justify-between text-lg font-medium text-gray-700">
                    <span>Tax (<?php echo $taxRatePercent; ?>%):</span>
                    <span><span id="taxAmount">0.00</span> <?php echo $defaultCurrency; ?></span>
                </div>
                <div class="flex justify-between text-2xl font-bold text-gray-800 mt-4">
                    <span>Total:</span>
                    <span><span id="totalAmount">0.00</span> <?php echo $defaultCurrency; ?></span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
            <button id="completeSaleBtn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105">
                Complete Sale
            </button>
            <button id="clearSaleBtn" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105">
                Clear Sale
            </button>
        </div>
    </div>
</div>

<!-- Custom Message Box for errors and confirmations -->
<div id="messageBox" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 transition-opacity duration-300">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white animate-fade-in">
        <h3 id="messageBoxTitle" class="text-lg font-bold">Message</h3>
        <div class="mt-2 px-7 py-3">
            <p id="messageBoxContent" class="text-sm text-gray-600">This is a message.</p>
        </div>
        <div class="mt-4 text-center">
            <button id="messageBoxCloseBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                Close
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.3s ease-out forwards;
    }
</style>

<script>
    // --- SALE MANAGEMENT SCRIPT ---

    // Constants
    const TAX_RATE = <?php echo $taxRatePercent; ?> / 100;
    const DEFAULT_CURRENCY = '<?php echo $defaultCurrency; ?>';

    // UI Elements
    const patientSearchInput = document.getElementById('patientSearchInput');
    const patientSearchResults = document.getElementById('patientSearchResults');
    const selectedPatientDisplay = document.getElementById('selectedPatientDisplay');
    const displayPatientName = document.getElementById('displayPatientName');
    const displayPatientId = document.getElementById('displayPatientId');
    const selectedPatientId = document.getElementById('selectedPatientId');

    const productSearchInput = document.getElementById('productSearchInput');
    const productSearchResults = document.getElementById('productSearchResults');
    const addProductBtn = document.getElementById('addProductBtn');
    const completeSaleBtn = document.getElementById('completeSaleBtn');
    const clearSaleBtn = document.getElementById('clearSaleBtn');
    const saleItemsList = document.getElementById('saleItemsList');
    const subtotalAmount = document.getElementById('subtotalAmount');
    const taxAmount = document.getElementById('taxAmount');
    const totalAmount = document.getElementById('totalAmount');
    const emptyCartMessage = document.getElementById('emptyCartMessage');
    const messageBox = document.getElementById('messageBox');
    const messageBoxTitle = document.getElementById('messageBoxTitle');
    const messageBoxContent = document.getElementById('messageBoxContent');
    const messageBoxCloseBtn = document.getElementById('messageBoxCloseBtn');

    // State
    let selectedPatient = null;
    let selectedProduct = null;
    let saleItems = [];
    let timeoutId;

    // --- UTILITY FUNCTIONS ---
    function showMessage(title, message) {
        if (messageBox && messageBoxTitle && messageBoxContent) {
            messageBoxTitle.textContent = title;
            messageBoxContent.textContent = message;
            messageBox.classList.remove('hidden');
        } else {
            console.error("Message box elements not found. Cannot display message.");
        }
    }

    function calculateTotals() {
        let subtotal = 0;
        saleItems.forEach(item => {
            subtotal += item.price * item.quantity;
        });

        const tax = subtotal * TAX_RATE;
        const total = subtotal + tax;

        return {
            subtotal: subtotal,
            tax: tax,
            total: total
        };
    }

    function updateTotals() {
        const totals = calculateTotals();
        subtotalAmount.textContent = totals.subtotal.toFixed(2);
        taxAmount.textContent = totals.tax.toFixed(2);
        totalAmount.textContent = totals.total.toFixed(2);

        if (saleItems.length > 0) {
            emptyCartMessage.classList.add('hidden');
        } else {
            emptyCartMessage.classList.remove('hidden');
        }
    }

    function renderSaleItems() {
        saleItemsList.innerHTML = '';
        saleItems.forEach((item, index) => {
            const itemElement = document.createElement('div');
            itemElement.className = 'flex items-center justify-between bg-white p-4 rounded-lg shadow-sm border border-gray-200';
            itemElement.innerHTML = `
                <div class="flex-1">
                    <p class="font-semibold text-gray-800">${item.product_name}</p>
                    <p class="text-sm text-gray-500">${DEFAULT_CURRENCY} ${item.price.toFixed(2)} x ${item.quantity}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button data-index="${index}" data-action="decrement" class="quantity-btn bg-gray-200 hover:bg-gray-300 text-gray-700 p-2 rounded-full w-8 h-8 flex items-center justify-center transition duration-200">-</button>
                    <span class="font-medium text-lg">${item.quantity}</span>
                    <button data-index="${index}" data-action="increment" class="quantity-btn bg-gray-200 hover:bg-gray-300 text-gray-700 p-2 rounded-full w-8 h-8 flex items-center justify-center transition duration-200">+</button>
                    <button data-index="${index}" data-action="remove" class="remove-btn text-red-500 hover:text-red-700 transition duration-200 transform hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            `;
            saleItemsList.appendChild(itemElement);
        });

        // Add event listeners for new buttons
        saleItemsList.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', handleQuantityChange);
        });
        saleItemsList.querySelectorAll('.remove-btn').forEach(button => {
            button.addEventListener('click', handleRemoveItem);
        });
    }

    // --- EVENT HANDLERS ---
    function searchPatients() {
        const searchTerm = patientSearchInput.value.trim();
        if (searchTerm.length < 2) {
            patientSearchResults.classList.add('hidden');
            return;
        }

        clearTimeout(timeoutId);
        timeoutId = setTimeout(async () => {
            try {
                const response = await fetch(`search_patients.php?search_term=${encodeURIComponent(searchTerm)}`);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server response not OK:', response.status, response.statusText);
                    console.error('Raw server response:', errorText);
                    throw new Error('Server returned an error.');
                }
                
                const patients = await response.json();
                renderPatientResults(patients);
            } catch (error) {
                console.error('Failed to fetch patients:', error);
                showMessage('Error', 'Failed to search for clients. Please check the console for more details and try again.');
                patientSearchResults.classList.add('hidden');
            }
        }, 300);
    }

    function renderPatientResults(patients) {
        patientSearchResults.innerHTML = '';
        if (patients.length > 0) {
            patients.forEach(patient => {
                const resultItem = document.createElement('div');
                resultItem.className = 'p-3 hover:bg-blue-100 cursor-pointer border-b border-gray-200 last:border-b-0';
                resultItem.textContent = `${patient.full_name} (${patient.patient_unique_id})`;
                resultItem.dataset.id = patient.patient_id;
                resultItem.dataset.name = patient.full_name;
                resultItem.addEventListener('click', () => {
                    selectPatient(patient);
                });
                patientSearchResults.appendChild(resultItem);
            });
            patientSearchResults.classList.remove('hidden');
        } else {
            patientSearchResults.classList.add('hidden');
        }
    }

    function selectPatient(patient) {
        selectedPatient = patient;
        selectedPatientId.value = patient.patient_id;
        displayPatientName.textContent = patient.full_name;
        displayPatientId.textContent = patient.patient_unique_id;
        selectedPatientDisplay.classList.remove('hidden');
        patientSearchResults.classList.add('hidden');
    }

    function searchProducts() {
        const searchTerm = productSearchInput.value.trim();
        if (searchTerm.length < 2) {
            productSearchResults.classList.add('hidden');
            return;
        }

        clearTimeout(timeoutId);
        timeoutId = setTimeout(async () => {
            try {
                const response = await fetch(`search_products.php?search_term=${encodeURIComponent(searchTerm)}`);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server response not OK:', response.status, response.statusText);
                    console.error('Raw server response:', errorText);
                    throw new Error('Server returned an error.');
                }
                
                const products = await response.json();
                renderProductResults(products);
            } catch (error) {
                console.error('Failed to fetch products:', error);
                showMessage('Error', 'Failed to search for products. Please check the console for more details and try again.');
                productSearchResults.classList.add('hidden');
            }
        }, 300);
    }

    function renderProductResults(products) {
        productSearchResults.innerHTML = '';
        if (products.length > 0) {
            products.forEach(product => {
                const resultItem = document.createElement('div');
                resultItem.className = 'p-3 hover:bg-blue-100 cursor-pointer border-b border-gray-200 last:border-b-0';
                resultItem.textContent = `${product.product_name} (${DEFAULT_CURRENCY} ${product.price}) - Stock: ${product.stock}`;
                resultItem.dataset.id = product.id;
                resultItem.dataset.name = product.product_name;
                resultItem.dataset.price = product.price;
                resultItem.dataset.stock = product.stock;
                resultItem.addEventListener('click', () => {
                    selectedProduct = product;
                    productSearchInput.value = product.product_name;
                    productSearchResults.classList.add('hidden');
                });
                productSearchResults.appendChild(resultItem);
            });
            productSearchResults.classList.remove('hidden');
        } else {
            productSearchResults.classList.add('hidden');
        }
    }

    function addProductToSale() {
        if (!selectedProduct) {
            showMessage('Missing Product', 'Please select a product from the search results first.');
            return;
        }

        // Check if product is already in the cart
        const existingItem = saleItems.find(item => item.id === selectedProduct.id);
        if (existingItem) {
            if (existingItem.quantity + 1 > selectedProduct.stock) {
                showMessage('Insufficient Stock', `Cannot add more of "${selectedProduct.product_name}". Only ${selectedProduct.stock} in stock.`);
                return;
            }
            existingItem.quantity++;
        } else {
            if (selectedProduct.stock < 1) {
                showMessage('Out of Stock', `"${selectedProduct.product_name}" is out of stock.`);
                return;
            }
            saleItems.push({
                id: selectedProduct.id,
                product_name: selectedProduct.product_name,
                price: parseFloat(selectedProduct.price),
                quantity: 1
            });
        }

        // Clear selection after adding
        selectedProduct = null;
        productSearchInput.value = '';
        renderSaleItems();
        updateTotals();
    }

    function handleQuantityChange(event) {
        const index = event.target.closest('button').dataset.index;
        const action = event.target.closest('button').dataset.action;
        const item = saleItems[index];

        if (!item) return;

        // Fetch the latest stock for the item
        fetch(`search_products.php?search_term=${encodeURIComponent(item.product_name)}`)
            .then(response => response.json())
            .then(products => {
                if (products.length > 0) {
                    const latestStock = products[0].stock;
                    if (action === 'increment') {
                        if (item.quantity + 1 <= latestStock) {
                            item.quantity++;
                        } else {
                            showMessage('Insufficient Stock', `Cannot add more. Only ${latestStock} in stock.`);
                        }
                    } else if (action === 'decrement' && item.quantity > 1) {
                        item.quantity--;
                    } else if (action === 'decrement' && item.quantity === 1) {
                        // If quantity is 1 and user decrements, remove the item
                        saleItems.splice(index, 1);
                    }
                } else {
                    showMessage('Error', 'Could not retrieve product information to check stock.');
                }
                renderSaleItems();
                updateTotals();
            })
            .catch(error => {
                console.error('Error fetching product for stock check:', error);
                showMessage('Error', 'Failed to check product stock. Please try again.');
            });
    }

    function handleRemoveItem(event) {
        const index = event.target.closest('button').dataset.index;
        saleItems.splice(index, 1);
        renderSaleItems();
        updateTotals();
    }

    async function completeSale() {
        if (!selectedPatient) {
            showMessage('Patient Not Selected', 'Please search for and select a patient to complete the sale.');
            return;
        }

        if (saleItems.length === 0) {
            showMessage('Empty Cart', 'Please add at least one product to complete the sale.');
            return;
        }

        const totals = calculateTotals();
        const saleData = {
            customer_type: 'Patient',
            patient_id: selectedPatient.patient_id,
            sale_items: JSON.stringify(saleItems),
            total_amount: totals.total.toFixed(2),
        };

        try {
            completeSaleBtn.disabled = true;
            completeSaleBtn.textContent = 'Processing...';
            completeSaleBtn.classList.add('bg-gray-500', 'cursor-not-allowed');

            console.log('Sending sale data:', saleData); // Log data being sent

            const response = await fetch('process_sale.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(saleData)
            });

            if (!response.ok) {
                // If the response is not ok, read the raw text and show it
                const errorText = await response.text();
                console.error('Server response not OK:', response.status, response.statusText);
                console.error('Raw server response:', errorText);
                showMessage('Sale Failed: Server Error', `The server responded with an error. Check the console for the full response and details.`);
                return;
            }

            const result = await response.json();
            console.log('Received response:', result); // Log the parsed JSON response

            if (result.success) {
                // Redirect to the new receipt page with the sale_id
                if (result.sale_id) {
                    window.location.href = `receipt.php?sale_id=${result.sale_id}`;
                } else {
                    showMessage('Sale Success', result.message);
                    clearSale();
                }
            } else {
                showMessage('Sale Failed', result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('Error', `An unexpected error occurred during sale processing. Details: ${error.message}. Check the console for more details.`);
        } finally {
            completeSaleBtn.disabled = false;
            completeSaleBtn.textContent = 'Complete Sale';
            completeSaleBtn.classList.remove('bg-gray-500', 'cursor-not-allowed');
        }
    }

    function clearSale() {
        saleItems = [];
        selectedProduct = null;
        selectedPatient = null;
        patientSearchInput.value = '';
        productSearchInput.value = '';
        selectedPatientDisplay.classList.add('hidden');
        renderSaleItems();
        updateTotals();
    }


    // --- EVENT LISTENERS ---
    document.addEventListener('DOMContentLoaded', () => {
        renderSaleItems();
        updateTotals();
    });

    patientSearchInput.addEventListener('input', searchPatients);
    productSearchInput.addEventListener('input', searchProducts);
    addProductBtn.addEventListener('click', addProductToSale);
    completeSaleBtn.addEventListener('click', completeSale);
    clearSaleBtn.addEventListener('click', clearSale);
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
