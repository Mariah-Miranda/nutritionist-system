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

        <!-- Patient Selection Section -->
        <div class="mb-8 p-6 bg-gray-50 rounded-xl shadow-inner">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">1. Select Patient</h3>
            
            <div id="patientSearchSection" class="relative">
                <label for="patientSearchInput" class="block text-sm font-medium text-gray-700 mb-2">Search by Name or ID</label>
                <input type="text" id="patientSearchInput" placeholder="e.g., John Doe or P-12345" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                <div id="patientSearchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 max-h-60 overflow-y-auto shadow-lg hidden">
                    <!-- Search results will be injected here -->
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
    const selectedPatientIdInput = document.getElementById('selectedPatientId'); // Renamed to avoid conflict

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
    /**
     * Displays a custom message box to the user.
     * @param {string} title - The title of the message box.
     * @param {string} message - The content message to display.
     */
    function showMessage(title, message) {
        if (messageBox && messageBoxTitle && messageBoxContent) {
            messageBoxTitle.textContent = title;
            messageBoxContent.textContent = message;
            messageBox.classList.remove('hidden');
        } else {
            console.error("Message box elements not found. Cannot display message.");
        }
    }

    /**
     * Calculates the subtotal, tax, and total for the current sale items.
     * @returns {{subtotal: number, tax: number, total: number}} An object containing the calculated totals.
     */
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

    /**
     * Updates the displayed subtotal, tax, and total amounts on the UI.
     * Also toggles the empty cart message visibility.
     */
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

    /**
     * Renders the list of sale items in the cart section.
     * Creates and appends HTML elements for each item, including quantity controls and remove buttons.
     */
    function renderSaleItems() {
        saleItemsList.innerHTML = ''; // Clear existing items
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
    /**
     * Handles patient search input. Debounces the input to reduce API calls.
     * Fetches patient data from 'search_patients.php' and renders results.
     */
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
                showMessage('Error', 'Failed to search for patients. Please check the console for more details and try again.');
                patientSearchResults.classList.add('hidden');
            }
        }, 300);
    }

    /**
     * Renders the patient search results in the dropdown.
     * @param {Array<Object>} patients - An array of patient objects.
     */
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

    /**
     * Selects a patient from the search results and updates the UI.
     * @param {Object} patient - The selected patient object.
     */
    function selectPatient(patient) {
        selectedPatient = patient;
        selectedPatientIdInput.value = patient.patient_id; // Use the renamed input
        displayPatientName.textContent = patient.full_name;
        displayPatientId.textContent = patient.patient_unique_id;
        selectedPatientDisplay.classList.remove('hidden');
        patientSearchResults.classList.add('hidden');
    }

    /**
     * Handles product search input. Debounces the input to reduce API calls.
     * Fetches product data from 'search_products.php' and renders results.
     */
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

    /**
     * Renders the product search results in the dropdown.
     * @param {Array<Object>} products - An array of product objects.
     */
    function renderProductResults(products) {
        productSearchResults.innerHTML = '';
        if (products.length > 0) {
            products.forEach(product => {
                const resultItem = document.createElement('div');
                resultItem.className = 'p-3 hover:bg-blue-100 cursor-pointer border-b border-gray-200 last:border-b-0';
                resultItem.textContent = `${product.product_name} (${DEFAULT_CURRENCY} ${parseFloat(product.price).toFixed(2)}) - Stock: ${product.stock}`;
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

    /**
     * Adds the currently selected product to the sale items list (cart).
     * Handles quantity increment if the product is already in the cart, and checks stock.
     */
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

    /**
     * Handles quantity changes (increment/decrement) and removal of items in the cart.
     * Fetches latest stock information before incrementing quantity.
     * @param {Event} event - The click event from the quantity or remove button.
     */
    function handleQuantityChange(event) {
        const button = event.target.closest('button');
        if (!button) return; // Ensure a button was clicked

        const index = button.dataset.index;
        const action = button.dataset.action;
        const item = saleItems[index];

        if (!item) return;

        // Fetch the latest stock for the item to ensure accurate stock check
        fetch(`search_products.php?search_term=${encodeURIComponent(item.product_name)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok for product stock check.');
                }
                return response.json();
            })
            .then(products => {
                if (products.length > 0) {
                    const latestStock = products[0].stock;
                    if (action === 'increment') {
                        if (item.quantity + 1 <= latestStock) {
                            item.quantity++;
                        } else {
                            showMessage('Insufficient Stock', `Cannot add more. Only ${latestStock} in stock.`);
                        }
                    } else if (action === 'decrement') {
                        if (item.quantity > 1) {
                            item.quantity--;
                        } else {
                            // If quantity is 1 and user decrements, remove the item
                            saleItems.splice(index, 1);
                        }
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

    /**
     * Removes an item from the sale items list (cart).
     * @param {Event} event - The click event from the remove button.
     */
    function handleRemoveItem(event) {
        const index = event.target.closest('button').dataset.index;
        saleItems.splice(index, 1); // Remove item from array
        renderSaleItems(); // Re-render the list
        updateTotals(); // Recalculate and update totals
    }

    /**
     * Processes the complete sale transaction.
     * Validates selections, sends sale data to the server, and handles responses.
     */
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
            customer_type: 'Patient', // Assuming 'Patient' for selected patient
            patient_id: selectedPatient.patient_id,
            sale_items: JSON.stringify(saleItems), // Send sale items as a JSON string
            total_amount: totals.total.toFixed(2),
            // You might want to add payment_method, discount_percent here if they are part of the form
            // For simplicity, we'll assume default values or handle them on the server for now.
        };

        try {
            // Disable button and show loading state
            completeSaleBtn.disabled = true;
            completeSaleBtn.textContent = 'Processing...';
            completeSaleBtn.classList.add('bg-gray-500', 'cursor-not-allowed');
            completeSaleBtn.classList.remove('hover:bg-blue-700', 'transform', 'hover:scale-105'); // Remove hover effects

            console.log('Sending sale data:', saleData); // Log data being sent

            const response = await fetch('process_sale.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded', // Standard for form submissions
                },
                body: new URLSearchParams(saleData) // Encode data for x-www-form-urlencoded
            });

            if (!response.ok) {
                // If the response is not ok (e.g., 400, 500 status), read the raw text and show it
                const errorText = await response.text();
                console.error('Server response not OK:', response.status, response.statusText);
                console.error('Raw server response:', errorText);
                showMessage('Sale Failed: Server Error', `The server responded with an error (${response.status}). Please try again or contact support. Details: ${errorText.substring(0, 100)}...`);
                return; // Stop execution
            }

            const result = await response.json(); // Attempt to parse JSON
            console.log('Received response:', result); // Log the parsed JSON response

            if (result.success) {
                showMessage('Sale Complete!', result.message || 'Sale processed successfully.');
                // Redirect to the new receipt page with the sale_id if provided
                if (result.sale_id) {
                    // Small delay to allow user to read success message before redirect
                    setTimeout(() => {
                        window.location.href = `receipt.php?sale_id=${result.sale_id}`;
                    }, 1500); 
                } else {
                    clearSale(); // Clear the form if no specific receipt page
                }
            } else {
                showMessage('Sale Failed', result.message || 'An unknown error occurred during sale processing.');
            }
        } catch (error) {
            console.error('Error:', error);
            // Differentiate between network errors and JSON parsing errors
            if (error instanceof SyntaxError) {
                showMessage('Error', `Failed to process server response (Invalid JSON). Check console for details.`);
            } else {
                showMessage('Error', `An unexpected error occurred: ${error.message}. Please try again.`);
            }
        } finally {
            // Re-enable button and restore original text/styles
            completeSaleBtn.disabled = false;
            completeSaleBtn.textContent = 'Complete Sale';
            completeSaleBtn.classList.remove('bg-gray-500', 'cursor-not-allowed');
            completeSaleBtn.classList.add('hover:bg-blue-700', 'transform', 'hover:scale-105'); // Restore hover effects
        }
    }

    /**
     * Clears all selected patient, products, and sale items, resetting the form.
     */
    function clearSale() {
        saleItems = [];
        selectedProduct = null;
        selectedPatient = null;
        patientSearchInput.value = '';
        productSearchInput.value = '';
        selectedPatientDisplay.classList.add('hidden'); // Hide patient display
        renderSaleItems(); // Clear rendered items
        updateTotals(); // Reset totals
        showMessage('Sale Cleared', 'The current sale has been cleared.');
    }


    // --- EVENT LISTENERS ---
    document.addEventListener('DOMContentLoaded', () => {
        renderSaleItems(); // Initial render of empty cart
        updateTotals(); // Initial update of totals (should be 0)
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
