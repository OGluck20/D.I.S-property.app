// Dashboard management class
class DashboardManager {
    constructor() {
        this.sidebar = document.querySelector('.dashboard-sidebar');
        this.menuToggle = document.querySelector('.menu-toggle');
        this.mainContent = document.querySelector('.dashboard-main');
        this.closeBtn = document.querySelector('.close-menu');
        this.contentSections = document.querySelectorAll('.content-section');
        this.navLinks = document.querySelectorAll('.sidebar-nav .nav-link[data-content]');
    }

    init() {
        this.setupEventListeners();
        this.showDefaultContent();
    }

    setupEventListeners() {
        // Navigation links
        this.navLinks.forEach(link => {
            link.addEventListener('click', (e) => this.handleNavigation(e, link));
        });

        // Mobile menu
        if (this.menuToggle && this.sidebar) {
            this.setupMobileMenu();
        }
    }

    handleNavigation(e, link) {
        e.preventDefault();
        
        // Update active states
        this.navLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');
        
        // Handle content display
        this.contentSections.forEach(section => section.style.display = 'none');
        
        const contentId = link.getAttribute('data-content');
        const selectedContent = document.getElementById(contentId);
        if (selectedContent) {
            selectedContent.style.display = 'block';
        }

        // Handle mobile view
        if (window.innerWidth < 992) {
            this.closeMobileMenu();
        }

        // Load specific content
        if (contentId === 'messages-content') {
            loadMessages();
        } else if (contentId === 'purchases-content') {
            loadPurchases();
        }
    }

    setupMobileMenu() {
        this.menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            this.openMobileMenu();
        });
        
        this.closeBtn.addEventListener('click', () => {
            this.closeMobileMenu();
        });
        
        document.addEventListener('click', (e) => {
            if (this.shouldCloseMobileMenu(e)) {
                this.closeMobileMenu();
            }
        });
    }

    openMobileMenu() {
        this.sidebar.classList.add('active');
        this.menuToggle.style.display = 'none';
        this.sidebar.style.display = 'block';
        this.mainContent.style.display = 'none';
    }

    closeMobileMenu() {
        this.sidebar.classList.remove('active');
        this.menuToggle.style.display = 'block';
        this.sidebar.style.display = 'none';
        this.mainContent.style.display = 'block';
    }

    shouldCloseMobileMenu(e) {
        return this.sidebar.classList.contains('active') && 
               !this.sidebar.contains(e.target) && 
               !this.menuToggle.contains(e.target);
    }

    showDefaultContent() {
        const defaultLink = document.querySelector('[data-content="home-content"]');
        if (defaultLink) {
            defaultLink.click();
        }
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const dashboard = new DashboardManager();
    dashboard.init();

    // Initialize application handlers
    setupApplicationHandlers();
});

// Application handling functions
function setupApplicationHandlers() {
    document.querySelectorAll('.apply-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const propertyId = this.dataset.propertyId;
            const plotSize = this.dataset.plotSize;
            openApplicationModal(propertyId, plotSize);
        });
    });

    const serviceTypeSelect = document.getElementById('serviceType');
    if (serviceTypeSelect) {
        serviceTypeSelect.addEventListener('change', handleServiceTypeChange);
    }
}

function handleServiceTypeChange() {
    const serviceType = this.value;
    const propertyId = document.getElementById('propertyId')?.value;
    const calculationResults = document.querySelector('.calculation-results');
    const proceedToPayBtn = document.getElementById('proceedToPay');

    if (!serviceType || !propertyId) {
        if (calculationResults) calculationResults.style.display = 'none';
        if (proceedToPayBtn) proceedToPayBtn.disabled = true;
        return;
    }

    calculateServiceFee(serviceType, propertyId);
}

function calculateServiceFee(serviceType, propertyId) {
    Promise.all([
        fetch(`handler/get_property_details.php?id=${propertyId}`).then(r => r.json()),
        fetch(`handler/get_service_fee.php?service=${serviceType}`).then(r => r.json())
    ])
    .then(([property, service]) => {
        const fee = service.fixed_fee > 0 
            ? service.fixed_fee 
            : property.plot_size * service.price_per_sqm;

        const calculationResults = document.querySelector('.calculation-results');
        const plotSizeDisplay = document.getElementById('plotSizeDisplay');
        const serviceFee = document.getElementById('serviceFee');
        const proceedToPayBtn = document.getElementById('proceedToPay');

        if (calculationResults) calculationResults.style.display = 'block';
        if (plotSizeDisplay) plotSizeDisplay.value = `${property.plot_size} sqm`;
        if (serviceFee) serviceFee.value = formatCurrency(fee);
        if (proceedToPayBtn) proceedToPayBtn.disabled = false;
    })
    .catch(error => {
        console.error('Fee calculation error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to calculate service fee. Please try again.'
        });
    });
}

document.getElementById('serviceType').addEventListener('change', async function() {
    try {
        const serviceType = this.value;
        const propertyId = document.getElementById('propertyId').value;
        
        if (!serviceType) {
            document.querySelector('.calculation-results').style.display = 'none';
            document.getElementById('proceedToPay').disabled = true;
            return;
        }

        // Get property details
        const propertyResponse = await fetch(`handler/get_property_details.php?id=${propertyId}`);
        const propertyText = await propertyResponse.text();
        
        if (propertyText.startsWith('<') || propertyText.includes('<br')) {
            throw new Error('Server returned invalid response');
        }
        
        const property = JSON.parse(propertyText);

        // Get service fee
        const serviceResponse = await fetch(`handler/get_service_fee.php?service=${serviceType}`);
        const serviceText = await serviceResponse.text();
        
        if (serviceText.startsWith('<') || serviceText.includes('<br')) {
            throw new Error('Invalid service response');
        }
        
        const service = JSON.parse(serviceText);

        // Calculate fee
        const fee = service.fixed_fee > 0 
            ? service.fixed_fee 
            : property.plot_size * service.price_per_sqm;

        // Update UI
        document.querySelector('.calculation-results').style.display = 'block';
        document.getElementById('plotSizeDisplay').value = `${property.plot_size} sqm`;
        document.getElementById('serviceFee').value = `₦${fee.toLocaleString()}`;
        document.getElementById('proceedToPay').disabled = false;

    } catch (error) {
        console.error('Fee calculation error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to calculate service fee. Please try again.'
        });
        document.querySelector('.calculation-results').style.display = 'none';
        document.getElementById('proceedToPay').disabled = true;
    }
});

function payWithPaystack(data) {
    const handler = PaystackPop.setup({
        key: 'your-public-key',
        email: data.email,
        amount: data.amount * 100, // Convert to kobo
        currency: 'NGN',
        ref: data.reference,
        callback: function(response) {
            // Handle successful payment
            fetch('handler/verify_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reference: response.reference })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Submit application
                    submitApplication(data);
                } else {
                    throw new Error(result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to submit application. Please try again.'
                });
            });
        },
        onClose: function() {
            // Handle popup closure
            alert('Transaction was not completed, window closed.');
        }
    });
    handler.openIframe();
}

function submitApplication(data) {
    console.log('Submitting application:', data);

    fetch('handler/submit_application.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const responseData = await response.json();
        console.log('Server response:', responseData);

        if (!response.ok) {
            throw new Error(responseData.message || 'Failed to submit application');
        }
        return responseData;
    })
    .then(result => {
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Your application has been submitted successfully.',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Close modal and reload page
                const modal = bootstrap.Modal.getInstance(document.getElementById('applicationModal'));
                if (modal) {
                    modal.hide();
                }
                location.reload();
            });
        } else {
            throw new Error(result.message || 'Failed to submit application');
        }
    })
    .catch(error => {
        console.error('Application submission error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to submit application. Please try again.'
        });
    });
}

function createAdminNotification(data) {
    fetch('handler/create_notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    });
}

// Add this to your existing DOMContentLoaded event listener
document.addEventListener('DOMContentLoaded', function() {
    // Content switching functionality
    const contentSections = document.querySelectorAll('.content-section');
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link[data-content]');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Hide all content sections
            contentSections.forEach(section => {
                section.style.display = 'none';
            });
            
            // Show the selected content section
            const contentId = this.getAttribute('data-content');
            document.getElementById(contentId).style.display = 'block';
            
            // On mobile, close the sidebar after selection
            if (window.innerWidth < 992) {
                const sidebar = document.querySelector('.dashboard-sidebar');
                const menuToggle = document.querySelector('.menu-toggle');
                if (sidebar && menuToggle) {
                    sidebar.classList.remove('active');
                    menuToggle.style.display = 'block';
                    sidebar.style.display = 'none';
                }
            }
        });
    });

    // Show default content (home) on page load
    document.querySelector('[data-content="home-content"]').click();
});

document.addEventListener('DOMContentLoaded', function() {
    // Profile update form submission
    document.getElementById('updateProfileForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Get form data
        const formData = {
            firstname: document.getElementById('firstname').value,
            lastname: document.getElementById('lastname').value,
            phone: document.getElementById('phone').value,
            email: document.getElementById('email').value
        };

        // Client-side validation
        if (!formData.firstname || !formData.lastname || !formData.phone || !formData.email) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fill in all required fields'
            });
            return;
        }

        // Phone number validation
        if (!/^\d{11}$/.test(formData.phone)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Phone Number',
                text: 'Please enter a valid 11-digit phone number'
            });
            return;
        }

        // Email validation
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Email',
                text: 'Please enter a valid email address'
            });
            return;
        }

        // Show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

        // Send update request
        fetch('handler/update_profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Your profile has been updated successfully'
                }).then(() => {
                    // Reload page to reflect changes
                    window.location.reload();
                });
            } else {
                throw new Error(data.message || 'Failed to update profile');
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message
            });
        })
        .finally(() => {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        });
    });

    // Change Password Form Handler
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('handler/change_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000
                }).then(() => {
                    this.reset();
                });
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message
            });
        });
    });
});



function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function loadPurchases() {
    const purchasesContainer = document.querySelector('.purchases-container');
    
    if (!purchasesContainer) {
        console.error('Purchases container not found');
        return;
    }

    purchasesContainer.innerHTML = `
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading purchases...</span>
            </div>
        </div>
    `;

    fetch('handler/fetch_purchases.php')
        .then(response => response.json())
        .then(data => {            
            if (!data.success) {
                throw new Error(data.message || 'Failed to load purchases');
            }

            if (!data.purchases || data.purchases.length === 0) {
                purchasesContainer.innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fs-3 mb-3 d-block"></i>
                        <p>You haven't made any purchases yet.</p>
                    </div>`;
                return;
            }

            purchasesContainer.innerHTML = renderPurchases(data.purchases);
            
            // Initialize buttons after rendering
            const buttons = document.querySelectorAll('.apply-service-btn');
            
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const propertyId = this.getAttribute('data-property-id');
                    const plotSize = this.getAttribute('data-plot-size');
                    openApplicationModal(propertyId, plotSize);
                });
            });
        })
        .catch(error => {
            console.error('Error loading purchases:', error);
            purchasesContainer.innerHTML = `
                <div class="alert alert-danger">
                    <p>${error.message}</p>
                    <button class="btn btn-primary mt-2" onclick="loadPurchases()">
                        <i class="fas fa-sync-alt me-2"></i>Retry
                    </button>
                </div>`;
        });
}

// Helper function to initialize property card features
function initializePropertyCards() {
    document.querySelectorAll('.apply-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const propertyId = this.dataset.propertyId;
            const plotSize = this.dataset.plotSize;
            openApplicationModal(propertyId, plotSize);
        });
    });
}

// Add event listener for purchases tab
document.addEventListener('DOMContentLoaded', function() {
    const purchasesLink = document.querySelector('[data-content="purchases-content"]');
    if (purchasesLink) {
        purchasesLink.addEventListener('click', loadPurchases);
    }
});

// Add this to your existing event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add click handler for purchases tab
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (this.getAttribute('data-content') === 'purchases-content') {
                loadPurchases();
            }
        });
    });
});

        // const plotSizeDisplay = document.getElementById('plotSizeDisplay'); // Unused variable
function openApplicationModal(propertyId, plotSize) {
    console.group('Opening Application Modal');
    try {
        console.log('Initializing modal for:', { propertyId, plotSize });
        
        // Get modal element
        const modalElement = document.getElementById('applicationModal');
        console.log('Modal element found:', !!modalElement);
        
        if (!modalElement) {
            throw new Error('Modal element not found in DOM');
        }

        // Check if Bootstrap is available
        console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
        if (typeof bootstrap === 'undefined') {
            throw new Error('Bootstrap is not loaded');
        }

        // Initialize Bootstrap modal
        console.log('Initializing Bootstrap modal');
        const modal = new bootstrap.Modal(modalElement);
        
        // Set property ID
        const propertyIdInput = document.getElementById('propertyId');
        console.log('Property ID input found:', !!propertyIdInput);
        if (propertyIdInput) {
            propertyIdInput.value = propertyId;
            console.log('Property ID set to:', propertyId);
        }

        // Reset form
        const form = document.getElementById('applicationForm');
        console.log('Application form found:', !!form);
        if (form) {
            form.reset();
            console.log('Form reset completed');
        }

        // Show the modal
        console.log('Attempting to show modal');
        modal.show();
        console.log('Modal show command executed');

    } catch (error) {
        console.error('Modal opening error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: `Failed to open application form: ${error.message}`
        });
    } finally {
        console.groupEnd();
    }
}

// Update the event listener initialization
function initializeApplicationButtons() {
    
    const buttons = document.querySelectorAll('.apply-service-btn');

    buttons.forEach((button, index) => {
        console.log(`Setting up button ${index + 1}:`, {
            propertyId: button.getAttribute('data-property-id'),
            plotSize: button.getAttribute('data-plot-size')
        });

        // Remove any existing listeners
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);

        newButton.addEventListener('click', function(e) {
            console.group('Button Click Handler');
            console.log('Button clicked:', {
                propertyId: this.getAttribute('data-property-id'),
                plotSize: this.getAttribute('data-plot-size')
            });

            e.preventDefault();
            e.stopPropagation();
            
            const propertyId = this.getAttribute('data-property-id');
            const plotSize = this.getAttribute('data-plot-size');
            
            if (!propertyId || !plotSize) {
                console.error('Missing required attributes:', { propertyId, plotSize });
                console.groupEnd();
                return;
            }

            openApplicationModal(propertyId, plotSize);
            console.groupEnd();
        });
    });

    console.groupEnd();
}

// Add this to your DOMContentLoaded event listener
document.addEventListener('DOMContentLoaded', function() {
        
    // Check for modal element
    const modalElement = document.getElementById('applicationModal');
    
    // Initialize all application buttons
    initializeApplicationButtons();
    
    // Initialize service type change handler
    const serviceType = document.getElementById('serviceType');
    if (serviceType) {
        serviceType.addEventListener('change', handleServiceTypeChange);
    }

    console.groupEnd();
});

// Add these functions to handle payment and cancellation
function setupApplicationModal() {
    const modal = document.getElementById('applicationModal');
    const cancelBtn = modal.querySelector('[data-bs-dismiss="modal"]');
    const proceedToPayBtn = document.getElementById('proceedToPay');
    
    // Handle cancel button
    cancelBtn.addEventListener('click', function() {
        resetApplicationForm();
    });

    // Handle proceed to pay button
    proceedToPayBtn.addEventListener('click', function() {
        const propertyId = document.getElementById('propertyId').value;
        const serviceType = document.getElementById('serviceType').value;
        const serviceFee = document.getElementById('serviceFee').value.replace('₦', '').replace(/,/g, '');
        
        initiatePayment(propertyId, serviceType, serviceFee);
    });
}

function resetApplicationForm() {
    const form = document.getElementById('applicationForm');
    const calculationResults = document.querySelector('.calculation-results');
    const proceedToPayBtn = document.getElementById('proceedToPay');
    
    form.reset();
    calculationResults.style.display = 'none';
    proceedToPayBtn.disabled = true;
}

function initiatePayment(propertyId, serviceType, amount) {
    // Initialize Paystack payment
    let handler = PaystackPop.setup({
        key: 'pk_test_e3d42791d57cebebbfae8dad035350fef02c0f8e', // Replace with your public key
        email: document.getElementById('userEmail').value, // Add a hidden input with user's email
        amount: parseFloat(amount) * 100, // Convert to kobo
        currency: 'NGN',
        ref: 'APP_' + Math.floor((Math.random() * 1000000000) + 1),
        callback: function(response) {
            submitApplication({
                propertyId: propertyId,
                serviceType: serviceType,
                amount: amount,
                reference: response.reference
            });
        },
        onClose: function() {
            Swal.fire({
                icon: 'info',
                title: 'Payment Cancelled',
                text: 'You have cancelled the payment process.',
                timer: 3000
            });
        }
    });
    
    handler.openIframe();
}

function submitApplication(paymentResponse) {
    console.group('Application Submission');
    try {
        // Validate payment response
        if (!paymentResponse || !paymentResponse.reference) {
            throw new Error('Invalid payment response');
        }

        const applicationData = {
            property_id: document.getElementById('propertyId').value,
            service_type: document.getElementById('serviceType').value,
            amount: parseFloat(document.getElementById('serviceFee').value.replace(/[^0-9.]/g, '')),
            reference: paymentResponse.reference,
            recipient_name: document.getElementById('recipientName').value,
            recipient_phone: document.getElementById('recipientPhone').value
        };

        // Validate application data
        for (const [key, value] of Object.entries(applicationData)) {
            if (!value && value !== 0) {
                throw new Error(`${key.replace(/_/g, ' ')} is required`);
            }
        }

        console.log('Sending application data:', applicationData);

        return fetch('handler/submit_application.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(applicationData)
        })
        .then(async response => {
            const responseText = await response.text();
            console.log('Raw server response:', responseText);

            try {
                return JSON.parse(responseText);
            } catch (e) {
                console.error('Failed to parse server response:', e);
                throw new Error('Invalid server response');
            }
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Application submission failed');
            }

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('applicationModal'));
            if (modal) {
                modal.hide();
            }

            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Your application has been submitted successfully.',
                showConfirmButton: true
            }).then(() => {
                location.reload();
            });
        })
        .catch(error => {
            console.error('Submission error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: error.message || 'There was an error submitting your application.',
                showConfirmButton: true
            });
        });

    } catch (error) {
        console.error('Validation error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: error.message,
            showConfirmButton: true
        });
    }
    console.groupEnd();
}

function createNotification(data) {
    fetch('handler/create_notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .catch(error => console.error('Notification error:', error));
}

// Initialize the application modal handlers when the document loads
document.addEventListener('DOMContentLoaded', function() {
    setupApplicationModal();
});


// Function to format currency
function formatCurrency(amount) {
    return '₦' + parseFloat(amount).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Update the handlePayment function in clients_dashboard.js
function handlePayment(amount, email) {
    const propertyId = document.getElementById('propertyId').value;
    const serviceType = document.getElementById('serviceType').value;
    const recipientName = document.getElementById('recipientName').value;
    const recipientPhone = document.getElementById('recipientPhone').value;

    if (!recipientName || !recipientPhone) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please fill in all recipient information'
        });
        return;
    }

    // Phone number validation
    const phoneRegex = /^[0-9]{11}$/;
    if (!phoneRegex.test(recipientPhone)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Phone Number',
            text: 'Please enter a valid 11-digit phone number'
        });
        return;
    }

    const handler = PaystackPop.setup({
        key: document.querySelector('meta[name="paystack-key"]').content,
        email: email,
        amount: amount * 100,
        currency: 'NGN',
        ref: 'APP_' + Math.floor((Math.random() * 1000000000) + 1),
        callback: function(response) {
            submitApplication(response.reference, {
                propertyId,
                serviceType,
                amount,
                recipientName,
                recipientPhone
            });
        },
        onClose: function() {
            Swal.fire({
                icon: 'warning',
                title: 'Payment Cancelled',
                text: 'The payment was cancelled. Please try again.'
            });
        }
    });
    handler.openIframe();
}

// Update the submitApplication function
function submitApplication(reference, data) {
    fetch('handler/submit_application.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            reference: reference,
            property_id: data.propertyId,
            service_type: data.serviceType,
            amount: data.amount,
            recipient_name: data.recipientName,
            recipient_phone: data.recipientPhone
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Application Submitted',
                text: 'Your application has been submitted successfully!'
            }).then(() => {
                $('#applicationModal').modal('hide');
                loadApplications(); // Refresh applications list
            });
        } else {
            throw new Error(data.message || 'Failed to submit application');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to submit application. Please try again.'
        });
    });
}

// Add this function for opening the application modal
function openApplicationModal(propertyId, plotSize) {
    try {
        console.log('Modal opened for property:', {
            propertyId: propertyId,
            plotSize: plotSize
        });

        // Get the modal element
        const applicationModal = new bootstrap.Modal(document.getElementById('applicationModal'));
        
        // Set the property ID
        document.getElementById('propertyId').value = propertyId;
        
        // Reset form
        document.getElementById('applicationForm').reset();
        
        // Show the modal
        applicationModal.show();

        // Setup the service type change handler
        const serviceTypeSelect = document.getElementById('serviceType');
        if (serviceTypeSelect) {
            serviceTypeSelect.addEventListener('change', function() {
                calculateServiceFee(this.value, propertyId, plotSize);
            });
        }

    } catch (error) {
        console.error('Error opening application modal:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to open application form. Please try again.'
        });
    }
}

// Add this function for calculating service fee
function calculateServiceFee(serviceType, propertyId, plotSize) {
    const calculationResults = document.querySelector('.calculation-results');
    const proceedToPayBtn = document.getElementById('proceedToPay');
    const plotSizeDisplay = document.getElementById('plotSizeDisplay');
    const serviceFeeInput = document.getElementById('serviceFee');

    if (!serviceType || !propertyId) {
        calculationResults.style.display = 'none';
        proceedToPayBtn.disabled = true;
        return;
    }

    // Show plot size
    plotSizeDisplay.value = `${plotSize} sqm`;
    calculationResults.style.display = 'block';

    // Calculate fee based on service type
    let fee = 0;
    switch (serviceType) {
        case 'C of O':
            fee = plotSize * 500; // Example rate
            break;
        case 'Survey':
            fee = plotSize * 300; // Example rate
            break;
        case 'BOQ':
            fee = 150000; // Fixed rate
            break;
    }

    // Display fee
    serviceFeeInput.value = formatCurrency(fee);
    proceedToPayBtn.disabled = false;
}

// Make sure you have this helper function
function formatCurrency(amount) {
    return '₦' + parseFloat(amount).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Add event listener for the apply button
document.addEventListener('DOMContentLoaded', function() {
    // Find all apply buttons
    const applyButtons = document.querySelectorAll('.apply-service-btn');
    
    applyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const propertyId = this.getAttribute('data-property-id');
            const plotSize = this.getAttribute('data-plot-size');
            openApplicationModal(propertyId, plotSize);
        });
    });
});

    // Update the purchases rendering code
    function renderPurchases(purchases) {
        return `
            <div class="row g-4 properties-grid">
                ${purchases.map(property => `
                    <div class="col-md-6 col-lg-4 property-item">
                        <div class="property-card">
                            <div class="property-image-wrapper">
                                <img src="uploads/properties/${property.media}" 
                                    alt="${property.title}"
                                    class="property-image"
                                    onerror="this.src='uploads/properties/default-property.jpg'">
                            </div>
                            <div class="property-details">
                                <h5 class="property-title text-truncate" title="${property.title}">${property.title}</h5>
                                <div class="property-info">
                                    <div class="price">${formatCurrency(property.price)}</div>
                                    <span class="plot-size">
                                        <i class="fas fa-ruler-combined me-1"></i>
                                        ${property.plot_size} sqm
                                    </span>
                                </div>
                                <div class="property-meta">
                                    <div class="purchase-date">
                                        Purchased: ${formatDate(property.updated_at)}
                                    </div>
                                </div>
                                <div class="property-actions">
                                    <button class="btn btn-primary apply-service-btn w-100" 
                                            data-property-id="${property.id}" 
                                            data-plot-size="${property.plot_size}"
                                            onclick="openApplicationModal('${property.id}', '${property.plot_size}')">
                                        <i class="fas fa-file-signature me-2"></i>
                                        <span>Apply for Service</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>`;
    }

// Update the application submission code
function submitApplication(paymentResponse) {
    console.group('Application Submission');
    try {
        // Validate payment response
        if (!paymentResponse || !paymentResponse.reference) {
            throw new Error('Invalid payment response');
        }

        const applicationData = {
            property_id: document.getElementById('propertyId').value,
            service_type: document.getElementById('serviceType').value,
            amount: parseFloat(document.getElementById('serviceFee').value.replace(/[^0-9.]/g, '')),
            reference: paymentResponse.reference,
            recipient_name: document.getElementById('recipientName').value,
            recipient_phone: document.getElementById('recipientPhone').value
        };

        // Validate application data
        const requiredFields = {
            property_id: 'Property ID',
            service_type: 'Service Type',
            amount: 'Amount',
            reference: 'Payment Reference',
            recipient_name: "Recipient's Name",
            recipient_phone: "Recipient's Phone"
        };

        for (const [key, label] of Object.entries(requiredFields)) {
            if (!applicationData[key] && applicationData[key] !== 0) {
                throw new Error(`${label} is required`);
            }
        }

        console.log('Sending application data:', applicationData);

        return fetch('handler/submit_application.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(applicationData),
            credentials: 'same-origin'
        })
        .then(async response => {
            const responseText = await response.text();
            console.log('Raw server response:', responseText);

            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('Failed to parse server response:', e);
                throw new Error('Invalid server response format');
            }

            if (!response.ok) {
                throw new Error(data.message || `Server error: ${response.status}`);
            }

            return data;
        })
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('applicationModal'));
                if (modal) {
                    modal.hide();
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Your application has been submitted successfully.',
                    showConfirmButton: true
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(data.message || 'Application submission failed');
            }
        })
        .catch(error => {
            console.error('Submission error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: error.message || 'There was an error submitting your application. Please try again.',
                showConfirmButton: true
            });
        })
        .finally(() => {
            console.groupEnd();
        });

    } catch (error) {
        console.error('Validation error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: error.message,
            showConfirmButton: true
        });
        console.groupEnd();
    }
}

// Update the payment initialization code
function initializePayment(amount) {
    console.group('Payment Initialization');
    try {
        if (!amount || isNaN(amount)) {
            throw new Error('Invalid amount');
        }

        const email = document.getElementById('userEmail').value;
        if (!email) {
            throw new Error('User email is required');
        }

        console.log('Initializing payment:', { amount, email });

        const handler = PaystackPop.setup({
            key: 'pk_test_e3d42791d57cebebbfae8dad035350fef02c0f8e',
            email: email,
            amount: Math.round(amount * 100), // Convert to kobo and ensure integer
            currency: 'NGN',
            ref: 'APP_' + Math.floor((Math.random() * 1000000000) + 1),
            onClose: () => {
                console.log('Payment window closed');
                Swal.fire({
                    icon: 'warning',
                    title: 'Payment Cancelled',
                    text: 'You have cancelled the payment process.'
                });
            },
            callback: (response) => {
                console.log('Payment callback received:', response);
                submitApplication(response);
            }
        });

        handler.openIframe();
        console.log('Payment iframe opened');

    } catch (error) {
        console.error('Payment initialization error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Payment Error',
            text: error.message
        });
    } finally {
        console.groupEnd();
    }
}

// Update the proceed to pay button handler
document.getElementById('proceedToPay').addEventListener('click', function(e) {
    e.preventDefault();
    
    // Validate form before proceeding
    const form = document.getElementById('applicationForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const amount = parseFloat(document.getElementById('serviceFee').value.replace(/[^0-9.]/g, ''));
    initializePayment(amount);
});