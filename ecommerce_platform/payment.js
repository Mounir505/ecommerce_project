// Payment page functionality
// Note: The Stripe publishable key should ideally be set via server-side configuration
//const stripe = Stripe('pk_test_YOUR_PUBLISHABLE_KEY');
//const elements = stripe.elements();

// Create card element
//const cardElement = elements.create('card', {
//    style: {
//        base: {
//            fontSize: '16px',
//            color: '#32325d',
//        }
//    }
//});

// Load cart data and update order summary on page load
document.addEventListener('DOMContentLoaded', () => {
    // Mount card element
   // cardElement.mount('#card-element');
    
    // Load cart from server-side session instead of localStorage
    loadCart();
    
    // Update order summary with cart items
    displayOrderSummary();
    
    // Initialize payment tabs
    initPaymentTabs();
    
    // Initialize PayPal buttons
    initPayPalButtons();
    
    // Initialize credit card form
    //initCreditCardForm();
});

// Global variable to store cart data
let cart = [];
let orderTotal = 0;
let currency = 'MAD'; // Store the currency for consistency

// Load cart data from PHP session via AJAX
function loadCart() {
    // Make an AJAX request to get cart data
    fetch('get_cart_data.php')
        .then(response => response.json())
        .then(data => {
            cart = data.items;
            orderTotal = data.total;
            currency = data.currency || 'MAD'; // Get currency from server if available
            displayOrderSummary();
        })
        .catch(error => {
            console.error('Error loading cart:', error);
        });
}

// Display order summary
function displayOrderSummary() {
    const orderItemsContainer = document.getElementById('order-items');
    const subtotalElement = document.getElementById('order-subtotal');
    const totalElement = document.getElementById('order-total');
    
    if (!orderItemsContainer || !subtotalElement || !totalElement) return;
    
    // Clear existing items
    orderItemsContainer.innerHTML = '';
    
    // Calculate subtotal
    let subtotal = 0;
    
    // Add items to order summary
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        const itemElement = document.createElement('div');
        itemElement.className = 'order-item';
        itemElement.innerHTML = `
            <div class="item-details">
                <img src="${item.image}" alt="${item.name}">
                <div class="item-info">
                    <h4>${item.name}</h4>
                    <p>Qty: ${item.quantity}</p>
                </div>
            </div>
            <div class="item-price">${itemTotal.toFixed(2)} ${currency}</div>
        `;
        
        orderItemsContainer.appendChild(itemElement);
    });
    
    // If orderTotal was passed from the server, use that instead
    subtotal = orderTotal > 0 ? orderTotal : subtotal;
    
    // Update totals
    subtotalElement.textContent = `${subtotal.toFixed(2)} ${currency}`;
    totalElement.textContent = `${subtotal.toFixed(2)} ${currency}`;
    
    // Store total for payment processing
    window.orderTotal = subtotal;
    
    // Update PayPal button amount if it exists
    const payButtons = document.querySelector('.payment-button');
    if (payButtons) {
        payButtons.textContent = `Pay Now (${subtotal.toFixed(2)} ${currency})`;
    }
}

// Initialize payment method tabs
function initPaymentTabs() {
    console.log("PayPal init called");
    const tabs = document.querySelectorAll('.payment-tab');
    const containers = document.querySelectorAll('.payment-method-container');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs and containers
            tabs.forEach(t => t.classList.remove('active'));
            containers.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab
            tab.classList.add('active');
            
            // Show corresponding container
            const method = tab.getAttribute('data-method');
            document.getElementById(`${method}-container`).classList.add('active');
        });
    });
}

// Initialize PayPal buttons
function initPayPalButtons() {
    if (!window.paypal) {
        console.error('PayPal SDK not loaded!');
        document.getElementById('paypal-button-container').innerHTML = '<div class="error-message">PayPal could not be loaded. Please try another payment method.</div>';
        return;
    }
    
    paypal.Buttons({
        // Configure environment - sandbox for testing, production for live
        // Style the buttons
        style: {
            layout: 'vertical',   // vertical or horizontal
            color:  'blue',       // gold, blue, silver, white, black
            shape:  'rect',       // pill or rect
            label:  'paypal',     // checkout, pay, buynow
            height: 40            // height in pixels
        },
        
        // Set up the transaction
        createOrder: function(data, actions) {
    // Show loading indicator
    showNotification('Preparing your payment...', 'info');
    
    // Verify all required shipping fields are filled
    const requiredFields = [
        { id: 'full-name', name: 'Full Name' },
        { id: 'email', name: 'Email' },
        { id: 'address', name: 'Address' },
        { id: 'city', name: 'City' },
        { id: 'zip', name: 'ZIP/Postal Code' },
        { id: 'country', name: 'Country' },
        { id: 'phone', name: 'Phone Number' }
    ];
    
    // Check each required field
    for (const field of requiredFields) {
        const element = document.getElementById(field.id);
        if (!element || !element.value.trim()) {
            // Show error and highlight missing field
            element.classList.add('error');
            showNotification(`Please fill in your ${field.name}`, 'error');
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return Promise.reject('Please complete all required fields');
        } else {
            // Remove error class if present
            element.classList.remove('error');
        }
    }
    
    // Ensure we have a valid total
    if (!window.orderTotal || window.orderTotal <= 0) {
        showNotification('Invalid order total. Please refresh the page.', 'error');
        return Promise.reject('Invalid order total');
    }
    
    // Get exchange rate from MAD to USD (or use stored value)
    return fetch('get_exchange_rate.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to get exchange rate');
            }
            
            // Convert MAD to USD using exchange rate from server
            const exchangeRate = data.rate || 0.0945; // Fallback rate if server issue
            
            // Calculate items first to avoid rounding errors
            const items = cart.map(item => {
                const unitPrice = (item.price * exchangeRate).toFixed(2);
                return {
                    name: item.name,
                    unit_amount: {
                        currency_code: 'USD',
                        value: unitPrice
                    },
                    quantity: item.quantity,
                    description: `Product ID: ${item.id}`
                };
            });
            
            // Calculate the actual total from items to ensure it matches
            let calculatedTotal = 0;
            items.forEach(item => {
                calculatedTotal += parseFloat(item.unit_amount.value) * item.quantity;
            });
            
            // Format to 2 decimal places to avoid floating point issues
            calculatedTotal = parseFloat(calculatedTotal.toFixed(2));
            
            // Store exchange rate info for later use when processing the order
            window.exchangeRateInfo = {
                rate: exchangeRate,
                from: data.from || 'MAD',
                to: data.to || 'USD',
                timestamp: data.last_updated || new Date().toISOString()
            };
            
            // Log for debugging
            console.log('Converting', window.orderTotal, window.currency, 'to USD');
            console.log('Rate:', exchangeRate, 'Calculated USD Amount:', calculatedTotal);
            
            // Create PayPal order
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        currency_code: 'USD', // PayPal requires USD or other supported currencies
                        value: calculatedTotal.toString(),
                        breakdown: {
                            item_total: {
                                currency_code: 'USD',
                                value: calculatedTotal.toString()
                            }
                        }
                    },
                    description: 'Purchase from Cara Store',
                    items: items
                }],
                application_context: {
                    shipping_preference: 'GET_FROM_FILE',
                    user_action: 'PAY_NOW'
                }
            });
        })
        .catch(error => {
            console.error('Error getting exchange rate:', error);
            showNotification('Error preparing payment. Please try again.', 'error');
            return Promise.reject('Error preparing payment');
        });
},
        
        // Finalize the transaction
        onApprove: function(data, actions) {
            // Show processing message
            showNotification('Processing your payment...', 'info');
            
            return actions.order.capture().then(function(orderData) {
                // Collect shipping information
                const shippingData = {
                    name: document.getElementById('full-name').value,
                    email: document.getElementById('email').value,
                    address: document.getElementById('address').value,
                    city: document.getElementById('city').value,
                    zip: document.getElementById('zip').value,
                    country: document.getElementById('country').value,
                    phone: document.getElementById('phone').value
                };
                
                // Capture relevant transaction details
                const transactionDetails = {
                    id: orderData.id,
                    status: orderData.status,
                    time: orderData.create_time,
                    payer: {
                        email: orderData.payer.email_address,
                        name: `${orderData.payer.name.given_name} ${orderData.payer.name.surname}`
                    },
                    amount: orderData.purchase_units[0].amount.value,
                    currency: orderData.purchase_units[0].amount.currency_code
                };
                
                // Send order data to server
                fetch('process-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        payment_method: 'paypal',
                        payment_data: transactionDetails,
                        shipping_info: shippingData,
                        order_items: cart,
                        order_total: window.orderTotal,
                        original_currency: window.currency,
                        exchange_rate_info: window.exchangeRateInfo || null
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear cart from session via AJAX
                        fetch('clear_cart.php')
                            .then(res => res.json())
                            .catch(err => console.error('Error clearing cart:', err));
                        
                        // Show success message
                        handlePaymentSuccess(transactionDetails);
                    } else {
                        showNotification(data.message || 'Order processing failed. Please contact support.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error processing your order. Please try again.', 'error');
                });
            });
        },
        
        // Handle cancellation
        onCancel: function() {
            showNotification('You cancelled the payment. Feel free to continue shopping!', 'info');
        },
        
        // Handle errors
        onError: function(err) {
            console.error('PayPal Error:', err);
            showNotification('Payment failed. Please try again or use another payment method.', 'error');
        }
    }).render('#paypal-button-container');
}

// Initialize credit card form
function initCreditCardForm() {
    const cardForm = document.getElementById('card-form');
    if (!cardForm) return;
    
    // Format card number with spaces
    const cardNumberInput = document.getElementById('card-number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function() {
            // Remove any non-digit characters
            let value = this.value.replace(/\D/g, '');
            
            // Add spaces after every 4 digits
            if (value.length > 0) {
                value = value.match(/.{1,4}/g).join(' ');
            }
            
            // Update input value
            this.value = value;
        });
    }
    
    // Format expiry date
    const expiryInput = document.getElementById('card-expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function() {
            // Remove any non-digit characters
            let value = this.value.replace(/\D/g, '');
            
            // Format as MM/YY
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            // Update input value
            this.value = value;
        });
    }
    
    // Handle form submission
    cardForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // In a real application, you would send this data to your payment processor
        // For this example, we'll simulate a successful payment
        
        // Show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.textContent = 'Processing...';
        submitButton.disabled = true;
        
        // Collect shipping and card info
        const shippingData = {
            name: document.getElementById('full-name').value,
            email: document.getElementById('email').value,
            address: document.getElementById('address').value,
            city: document.getElementById('city').value,
            zip: document.getElementById('zip').value,
            country: document.getElementById('country').value,
            phone: document.getElementById('phone').value
        };
        
        const cardData = {
            number: document.getElementById('card-number').value.replace(/\s/g, ''),
            expiry: document.getElementById('card-expiry').value,
            cvc: document.getElementById('card-cvc').value,
            name: document.getElementById('card-name').value
        };
        
        // Send to server for processing
        fetch('process-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                payment_method: 'card',
                card_data: cardData,
                shipping_info: shippingData,
                order_total: window.orderTotal,
                original_currency: currency
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                handlePaymentSuccess({
                    id: data.order_id,
                    status: 'COMPLETED',
                    payment_source: {
                        card: {
                            last_digits: cardData.number.slice(-4)
                        }
                    }
                });
            } else {
                showNotification(data.message || 'Payment processing failed. Please try again.', 'error');
            }
            
            // Reset button
            submitButton.textContent = originalText;
            submitButton.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error processing your payment. Please try again.', 'error');
            
            // Reset button
            submitButton.textContent = originalText;
            submitButton.disabled = false;
        });
    });
}

// Handle successful payment
function handlePaymentSuccess(orderData) {
    // Save order to order history (optional)
    saveOrderToHistory(orderData);
    
    // Redirect to success page or show success message
    showPaymentSuccessMessage(orderData);
}

// Save order to order history
function saveOrderToHistory(orderData) {
    // Get existing orders from localStorage
    let orderHistory = localStorage.getItem('caraOrderHistory');
    if (orderHistory) {
        orderHistory = JSON.parse(orderHistory);
    } else {
        orderHistory = [];
    }
    
    // Create new order object
    const newOrder = {
        id: orderData.id,
        date: new Date().toISOString(),
        items: cart,
        total: window.orderTotal,
        status: 'processing',
        payment: {
            method: document.querySelector('.payment-tab.active').getAttribute('data-method'),
            id: orderData.id,
            status: orderData.status
        },
        shipping: {
            name: document.getElementById('full-name').value,
            address: document.getElementById('address').value,
            city: document.getElementById('city').value,
            zip: document.getElementById('zip').value,
            country: document.getElementById('country').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value
        }
    };
    
    // Add to order history
    orderHistory.push(newOrder);
    
    // Save to localStorage
    localStorage.setItem('caraOrderHistory', JSON.stringify(orderHistory));
}

// Show payment success message
function showPaymentSuccessMessage(orderData) {
    // Replace page content with success message
    const paymentContainer = document.querySelector('.payment-container');
    if (!paymentContainer) return;
    
    // Create success message
    const successMessage = document.createElement('div');
    successMessage.className = 'payment-success';
    successMessage.innerHTML = `
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Payment Successful!</h2>
        <p>Thank you for your purchase. Your order has been confirmed.</p>
        <div class="order-info">
            <p><strong>Order Number:</strong> ${orderData.id}</p>
            <p><strong>Order Date:</strong> ${new Date().toLocaleDateString()}</p>
            <p><strong>Total Amount:</strong> ${window.orderTotal.toFixed(2)} ${currency}</p>
        </div>
        <p>A confirmation email has been sent to ${document.getElementById('email').value}</p>
        <div class="action-buttons">
            <a href="index.php" class="normal">Return to Home</a>
            <a href="shop.php" class="normal">Continue Shopping</a>
        </div>
    `;
    
    // Replace content
    paymentContainer.innerHTML = '';
    paymentContainer.appendChild(successMessage);
    
    // Scroll to top
    window.scrollTo(0, 0);
}

// Show notification
function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Fade in
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 3000);
}