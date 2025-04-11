// Create a WeakMap to store event handlers
const eventHandlers = new WeakMap();

document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar navigation
    initializeSidebar();
    
    // Initialize revenue chart
    initializeRevenueChart();
    
    // Initialize other handlers
    initializePeriodSelectors();
    initializePropertyHandlers();
    initializeUserHandlers();
    initializeDeviceHandlers();
    initializeApplicationHandlers();
    initializeNotifications();
    initializeBlogSection(); // Call only once here

    // Initialize visitor section when dashboard is shown
    document.querySelector('[data-section="dashboard"]').addEventListener('click', function() {
        if (document.getElementById('dashboard-section').style.display !== 'none') {
            loadVisitorAnalytics();
        }
    });
    
    // Load visitor analytics on initial page load if dashboard is visible
    if (document.getElementById('dashboard-section').style.display !== 'none') {
        loadVisitorAnalytics();
    }
});

function initializeSidebar() {
    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionName = this.getAttribute('data-section');
            
            // Hide all content sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show target section
            const targetSection = document.getElementById(`${sectionName}-section`);
            if (targetSection) {
                targetSection.style.display = 'block';
            }

            // Update active state in sidebar
            document.querySelectorAll('.sidebar-link').forEach(link => {
                link.classList.remove('active');
            });
            this.classList.add('active');

            // Special handling for dashboard to refresh chart
            if (sectionName === 'dashboard' && window.revenueChart) {
                window.revenueChart.update();
            }
        });
    });

    // Show dashboard by default
    document.getElementById('dashboard-section').style.display = 'block';
}

function initializeRevenueChart() {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    window.revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Revenue',
                data: [],
                borderColor: '#2ecc71',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(46, 204, 113, 0.1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₦' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Load initial data
    loadRevenueData('weekly');

    // Add period selector event listeners
    document.querySelectorAll('.period-selector button').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.period-selector button').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            loadRevenueData(this.dataset.period);
        });
    });
}

function formatCurrency(value) {
    // Handle different scales
    if (Math.abs(value) >= 1e12) {
        return '₦' + (value / 1e12).toFixed(2) + 'T';
    } else if (Math.abs(value) >= 1e9) {
        return '₦' + (value / 1e9).toFixed(2) + 'B';
    } else if (Math.abs(value) >= 1e6) {
        return '₦' + (value / 1e6).toFixed(2) + 'M';
    } else if (Math.abs(value) >= 1e3) {
        return '₦' + (value / 1e3).toFixed(2) + 'K';
    } else {
        return '₦' + value.toFixed(2);
    }
}

function updateRevenueChart(data, period) {
    if (!window.revenueChart) return;

    // Find the maximum value to determine scale
    const maxValue = Math.max(...data.map(item => parseFloat(item.total)));
    
    window.revenueChart.options.scales.y.max = getChartMaxValue(maxValue);
    window.revenueChart.options.periodType = period;

    const labels = data.map(item => {
        switch(period) {
            case 'weekly':
                return new Date(item.date).toLocaleDateString();
            case 'monthly':
                return new Date(item.date + '-01').toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
            case 'yearly':
                return item.date;
            default:
                return item.date;
        }
    });

    window.revenueChart.data.labels = labels;
    window.revenueChart.data.datasets[0].data = data.map(item => item.total);
    window.revenueChart.update();
}

function getChartMaxValue(maxValue) {
    // Round up to the next nice number based on the scale
    if (maxValue >= 1e12) { // Trillion
        return Math.ceil(maxValue / 1e12) * 1e12;
    } else if (maxValue >= 5e11) { // Half Trillion
        return Math.ceil(maxValue / 1e11) * 1e11;
    } else if (maxValue >= 1e11) { // Hundred Billion
        return Math.ceil(maxValue / 1e11) * 1e11;
    } else if (maxValue >= 1e9) { // Billion
        return Math.ceil(maxValue / 1e9) * 1e9;
    } else if (maxValue >= 5e8) { // Half Billion
        return Math.ceil(maxValue / 1e8) * 1e8;
    } else if (maxValue >= 1e8) { // Hundred Million
        return Math.ceil(maxValue / 1e8) * 1e8;
    } else if (maxValue >= 1e6) { // Million
        return Math.ceil(maxValue / 1e6) * 1e6;
    } else if (maxValue >= 5e5) { // Half Million
        return Math.ceil(maxValue / 1e5) * 1e5;
    } else if (maxValue >= 1e5) { // Hundred Thousand
        return Math.ceil(maxValue / 1e5) * 1e5;
    } else if (maxValue >= 1e3) { // Thousand
        return Math.ceil(maxValue / 1e3) * 1e3;
    } else {
        return Math.ceil(maxValue / 100) * 100;
    }
}

function initializePeriodSelectors() {
    const periodButtons = document.querySelectorAll('.period-selector .btn');
    console.log('Found period buttons:', periodButtons.length);

    periodButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const period = this.getAttribute('data-period');
            console.log('Switching to period:', period);

            // Update active state
            periodButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Load new data
            loadRevenueData(period);
        });
    });
}

async function loadRevenueData(period) {
    try {
        const response = await fetch(`handler/get_revenue_data.php?period=${period}`);
        const data = await response.json();
        
        if (data.success) {
            const chartData = data.data.map(item => {
                // Parse date string properly
                let date;
                if (period === 'yearly') {
                    // For yearly, just use the year directly
                    return {
                        date: item.date, // Assuming item.date is just the year
                        total: parseFloat(item.total) || 0
                    };
                } else {
                    // For weekly and monthly, parse the full date
                    date = new Date(item.date);
                    if (isNaN(date.getTime())) {
                        console.error('Invalid date:', item.date);
                        date = new Date(); // Fallback to current date
                    }
                    return {
                        date: date,
                        total: parseFloat(item.total) || 0
                    };
                }
            });

            // Sort data by date
            chartData.sort((a, b) => {
                if (period === 'yearly') {
                    return parseInt(a.date) - parseInt(b.date);
                }
                return a.date - b.date;
            });

            // Format labels based on period
            const labels = chartData.map(item => {
                if (period === 'yearly') {
                    return item.date; // Just return the year
                }
                
                const date = item.date;
                switch(period) {
                    case 'weekly':
                        return date.toLocaleDateString('en-GB', { 
                            day: 'numeric', 
                            month: 'short' 
                        });
                    case 'monthly':
                        return date.toLocaleDateString('en-GB', { 
                            month: 'short',
                            year: 'numeric'
                        });
                    default:
                        return date.toLocaleDateString();
                }
            });

            const values = chartData.map(item => item.total);

            // Debug output
            console.log('Period:', period);
            console.log('Labels:', labels);
            console.log('Values:', values);

            // Update chart
            if (window.revenueChart) {
                window.revenueChart.data.labels = labels;
                window.revenueChart.data.datasets[0].data = values;
                window.revenueChart.update();
            }
        }
    } catch (error) {
        console.error('Error loading revenue data:', error);
    }
}

// Add these functions for property management
function initializePropertyHandlers() {
    // Add Property
    document.getElementById('savePropertyBtn')?.addEventListener('click', async function() {
        const form = document.getElementById('addPropertyForm');
        const formData = new FormData(form);

        try {
            const response = await fetch('handler/add_property.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Property added successfully'
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to add property'
            });
        }
    });

    // Edit Property
    document.querySelectorAll('.edit-property').forEach(button => {
        button.addEventListener('click', async function() {
            const propertyId = this.dataset.id;
            try {
                const response = await fetch(`handler/get_property.php?id=${propertyId}`);
                const data = await response.json();
                
                if (data.success) {
                    populateEditForm(data.property);
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                Swal.fire('Error', error.message || 'Failed to load property details', 'error');
            }
        });
    });

    // Update Property
    document.getElementById('updatePropertyBtn')?.addEventListener('click', async function() {
        const form = document.getElementById('editPropertyForm');
        const formData = new FormData(form);

        try {
            const response = await fetch('handler/update_property.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                await Swal.fire('Success', 'Property updated successfully', 'success');
                location.reload();
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire('Error', error.message || 'Failed to update property', 'error');
        }
    });

    // Delete Property
    document.querySelectorAll('.delete-property').forEach(button => {
        button.addEventListener('click', async function() {
            const propertyId = this.dataset.id;
            
            try {
                if (typeof Swal !== 'undefined') {
                    const result = await Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    });

                    if (!result.isConfirmed) return;
                } else if (!confirm('Are you sure you want to delete this property?')) {
                    return;
                }

                const response = await fetch('handler/delete_property.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: propertyId })
                });
                const data = await response.json();

                if (data.success) {
                    showAlert('Deleted!', 'Property has been deleted.', 'success');
                    location.reload();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                showAlert('Error', error.message || 'Failed to delete property');
            }
        });
    });
}

function populateEditForm(property) {
    document.getElementById('edit_property_id').value = property.id;
    document.getElementById('edit_title').value = property.title;
    document.getElementById('edit_price').value = property.price;
    document.getElementById('edit_description').value = property.description;
    document.getElementById('edit_plot_size').value = property.plot_size;
    document.getElementById('edit_address').value = property.address;
    document.getElementById('edit_city').value = property.city;
    document.getElementById('edit_state').value = property.state;
    document.getElementById('edit_zip_code').value = property.zip_code;
}

function showAlert(title, message, type = 'error') {
    if (typeof Swal !== 'undefined') {
        Swal.fire(title, message, type);
    } else {
        alert(`${title}: ${message}`);
    }
}

// Add these functions for user management
function initializeUserHandlers() {
    // Add View User Handler with updated modal content
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-user')) {
            const button = e.target.closest('.view-user');
            const userId = button.dataset.id;
            
            const modalElement = document.getElementById('viewUserModal');
            if (!modalElement) {
                console.error('Modal element not found');
                return;
            }

            // Close any existing modal and remove backdrop
            const existingModal = bootstrap.Modal.getInstance(modalElement);
            if (existingModal) {
                existingModal.dispose();
            }

            fetch(`handler/get_user_details.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.data;
                        
                        // Initialize new modal
                        const modal = new bootstrap.Modal(modalElement, {
                            backdrop: true,
                            keyboard: true
                        });

                        // Update content before showing modal
                        const elements = {
                            name: modalElement.querySelector('#userDetailName'),
                            email: modalElement.querySelector('#userDetailEmail'),
                            phone: modalElement.querySelector('#userDetailPhone'),
                            gender: modalElement.querySelector('#userDetailGender'),
                            joined: modalElement.querySelector('#userDetailJoined'),
                            added: modalElement.querySelector('#userDetailPropertiesAdded'),
                            purchased: modalElement.querySelector('#userDetailPropertiesPurchased')
                        };

                        // Update content with animation
                        Object.entries(elements).forEach(([key, element]) => {
                            if (!element) {
                                console.error(`Element not found: ${key}`);
                                return;
                            }

                            element.style.opacity = '0';
                            
                            switch(key) {
                                case 'name':
                                    element.textContent = `${user.firstname} ${user.lastname}`;
                                    break;
                                case 'email':
                                    element.textContent = user.email;
                                    break;
                                case 'phone':
                                    element.textContent = user.phone || 'Not provided';
                                    break;
                                case 'gender':
                                    // Capitalize first letter of gender and handle "not verified" case
                                    element.textContent = user.gender && user.gender !== 'not verified' ? 
                                        user.gender.charAt(0).toUpperCase() + user.gender.slice(1) : 
                                        'Not verified';
                                    break;
                                case 'joined':
                                    element.textContent = new Date(user.created_at)
                                        .toLocaleDateString('en-GB', {
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric'
                                        });
                                    break;
                                case 'added':
                                    element.textContent = user.properties_added || '0';
                                    break;
                                case 'purchased':
                                    element.textContent = user.properties_purchased || '0';
                                    break;
                            }

                            // Fade in animation
                            setTimeout(() => {
                                element.style.transition = 'opacity 0.3s ease-in-out';
                                element.style.opacity = '1';
                            }, 100);
                        });

                        // Add modal close event handler
                        modalElement.addEventListener('hidden.bs.modal', function() {
                            document.body.classList.remove('modal-open');
                            const backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) {
                                backdrop.remove();
                            }
                            modal.dispose();
                        }, { once: true });

                        modal.show();
                    } else {
                        throw new Error(data.message || 'Failed to load user details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to load user details',
                        confirmButtonColor: '#3085d6'
                    });
                });
        }
    });

    // Toggle User Status
    document.querySelectorAll('.toggle-status').forEach(button => {
        button.addEventListener('click', async function() {
            const userId = this.dataset.id;
            const currentStatus = this.dataset.currentStatus;
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            
            try {
                const response = await fetch('handler/update_user_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        user_id: userId,
                        status: newStatus 
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                showAlert('Error', error.message || 'Failed to update user status');
            }
        });
    });

    // Add Delete User Handler
    document.addEventListener('click', async function(e) {
        if (e.target.closest('.delete-user')) {
            const button = e.target.closest('.delete-user');
            const userId = button.dataset.id;
            
            try {
                const result = await Swal.fire({
                    title: 'Are you sure?',
                    text: "This user will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete user'
                });

                if (result.isConfirmed) {
                    const response = await fetch('handler/delete_user.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: userId })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        await Swal.fire('Deleted!', 'User has been deleted.', 'success');
                        loadUsers(); // Reload the users table
                    } else {
                        throw new Error(data.message);
                    }
                }
            } catch (error) {
                Swal.fire('Error', error.message || 'Failed to delete user', 'error');
            }
        }
    });
}

// Update the loadUsers function
function loadUsers() {
    fetch('api/get_users.php')
        .then(response => response.json())
        .then(users => {
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = '';
            
            users.forEach(user => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.firstname} ${user.lastname}</td>
                    <td>${user.email}</td>
                    <td>
                        <div class="phone-number">
                            ${user.phone}
                            <button class="btn btn-sm btn-link copy-phone" data-phone="${user.phone}">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </td>
                    <td>${new Date(user.created_at).toLocaleDateString('en-GB', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric'
                    })}</td>
                    <td>
                        <button class="btn btn-sm btn-info view-user" 
                                data-id="${user.id}" 
                                data-bs-toggle="modal" 
                                data-bs-target="#viewUserModal">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-user" 
                                data-id="${user.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Initialize copy buttons
            initializeCopyButtons();
        })
        .catch(error => {
            console.error('Error loading users:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load users'
            });
        });
}

function initializeDeviceHandlers() {
    // Add Device Handler
    document.getElementById('saveDeviceBtn')?.addEventListener('click', async function() {
        const form = document.getElementById('addDeviceForm');
        if (!form) return;

        try {
            const formData = new FormData(form);
            
            const response = await fetch('handler/add_device.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                await Swal.fire('Success', 'Device added successfully', 'success');
                const modal = bootstrap.Modal.getInstance(document.getElementById('addDeviceModal'));
                modal.hide();
                form.reset();
                location.reload(); // Reload to show new device
            } else {
                throw new Error(result.message || 'Failed to add device');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', error.message, 'error');
        }
    });

    // Delete Device Handler
    document.addEventListener('click', async function(e) {
        const deleteBtn = e.target.closest('.delete-device');
        if (!deleteBtn) return;

        const deviceId = deleteBtn.dataset.id;
        
        try {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "This device will be permanently deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            });

            if (result.isConfirmed) {
                const response = await fetch('handler/delete_device.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: deviceId })
                });
                
                const data = await response.json();
                if (data.success) {
                    await Swal.fire('Deleted!', 'Device has been deleted.', 'success');
                    location.reload(); // Reload to update the list
                } else {
                    throw new Error(data.message || 'Failed to delete device');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', error.message, 'error');
        }
    });

    // Edit Device Handler
    document.addEventListener('click', async function(e) {
        const editBtn = e.target.closest('.edit-device');
        if (!editBtn) return;

        const deviceId = editBtn.dataset.id;
        
        try {
            const response = await fetch(`handler/get_device.php?id=${deviceId}`);
            const data = await response.json();
            
            if (data.success) {
                const device = data.data;
                const form = document.getElementById('editDeviceForm');
                
                // Populate form fields
                form.querySelector('[name="id"]').value = device.id;
                form.querySelector('[name="name"]').value = device.name;
                form.querySelector('[name="brand"]').value = device.brand;
                form.querySelector('[name="ram"]').value = device.ram;
                form.querySelector('[name="storage"]').value = device.storage;
                form.querySelector('[name="price"]').value = device.price;
            } else {
                throw new Error(data.message || 'Failed to load device details');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', error.message, 'error');
        }
    });

    // Update Device Handler
    document.getElementById('updateDeviceBtn')?.addEventListener('click', async function() {
        const form = document.getElementById('editDeviceForm');
        if (!form) return;

        try {
            const formData = new FormData(form);
            
            const response = await fetch('handler/update_device.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                await Swal.fire('Success', 'Device updated successfully', 'success');
                // Use window.location.href for a fresh reload
                window.location.href = window.location.href;
            } else {
                throw new Error(result.message || 'Failed to update device');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', error.message, 'error');
        }
    });
}

function initializeApplicationHandlers() {
    // Filter Buttons
    document.querySelectorAll('.filter-buttons .btn').forEach(button => {
        button.addEventListener('click', function() {
            // Update active state
            document.querySelectorAll('.filter-buttons .btn').forEach(btn => 
                btn.classList.remove('active'));
            this.classList.add('active');

            // Filter rows
            const filter = this.dataset.filter;
            document.querySelectorAll('#applicationsTableBody tr').forEach(row => {
                if (filter === 'all' || row.dataset.status === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // Complete Application Handler
    document.addEventListener('click', async function(e) {
        if (e.target.closest('.complete-application')) {
            const button = e.target.closest('.complete-application');
            const applicationId = button.dataset.id;
            
            try {
                const result = await Swal.fire({
                    title: 'Complete Application',
                    text: "This will mark the application as completed. Continue?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, complete it'
                });

                if (result.isConfirmed) {
                    const response = await fetch('handler/complete_application.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: applicationId })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        await Swal.fire('Completed!', 'Application has been completed.', 'success');
                        location.reload();
                    } else {
                        throw new Error(data.message);
                    }
                }
            } catch (error) {
                Swal.fire('Error', error.message || 'Failed to complete application', 'error');
            }
        }
    });

    // Delete Application Handler
    document.addEventListener('click', async function(e) {
        if (e.target.closest('.delete-application')) {
            const button = e.target.closest('.delete-application');
            const applicationId = button.dataset.id;
            
            try {
                const result = await Swal.fire({
                    title: 'Are you sure?',
                    text: "This application will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it'
                });

                if (result.isConfirmed) {
                    const response = await fetch('handler/delete_application.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: applicationId })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        await Swal.fire('Deleted!', 'Application has been deleted.', 'success');
                        location.reload();
                    } else {
                        throw new Error(data.message);
                    }
                }
            } catch (error) {
                Swal.fire('Error', error.message || 'Failed to delete application', 'error');
            }
        }
    });

    // Add to your initializeApplicationHandlers function
    document.addEventListener('click', function(e) {
        if (e.target.closest('.copy-phone')) {
            const button = e.target.closest('.copy-phone');
            const phone = button.dataset.phone;
            
            navigator.clipboard.writeText(phone).then(() => {
                // Show a brief tooltip or notification
                const originalTitle = button.getAttribute('title');
                button.setAttribute('title', 'Copied!');
                button.innerHTML = '<i class="fas fa-check"></i>';
                
                setTimeout(() => {
                    button.setAttribute('title', originalTitle);
                    button.innerHTML = '<i class="fas fa-copy"></i>';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
    });
}

function initializeNotifications() {
    // Mark single notification as read
    document.addEventListener('click', async function(e) {
        const notifItem = e.target.closest('.notification-item.unread');
        if (notifItem) {
            const notifId = notifItem.dataset.id;
            try {
                const response = await fetch('handler/notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=mark_read&id=${notifId}`
                });
                
                const data = await response.json();
                if (data.success) {
                    notifItem.classList.remove('unread');
                    updateNotificationBadge();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }
    });

    // Mark all notifications as read
    document.querySelector('.mark-all-read')?.addEventListener('click', async function() {
        try {
            const response = await fetch('handler/notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_all_read'
            });
            
            const data = await response.json();
            if (data.success) {
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                });
                updateNotificationBadge();
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    });

    // Update notification count periodically
    setInterval(async function() {
        try {
            const response = await fetch('handler/notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_unread_count'
            });
            
            const data = await response.json();
            updateNotificationBadge(data.count);
        } catch (error) {
            console.error('Error getting notification count:', error);
        }
    }, 30000); // Check every 30 seconds
}

function updateNotificationBadge(count = null) {
    let badge = document.querySelector('.notification-badge');
    const bellIcon = document.querySelector('.notification-bell');
    
    if (count === null) {
        count = document.querySelectorAll('.notification-item.unread').length;
    }
    
    if (count === 0) {
        if (badge) badge.remove();
    } else {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'notification-badge';
            bellIcon.appendChild(badge);
        }
        badge.textContent = count;
    }
}

// Add error logging
window.onerror = function(msg, url, lineNo, columnNo, error) {
    console.error('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError object: ' + JSON.stringify(error));
    return false;
};

function initializeBlogSection() {
    // Only initialize once by checking if we've already initialized
    if (window.blogSectionInitialized) {
        console.log('Blog section already initialized');
        return;
    }
    
    // Load initial blog posts
    loadBlogPosts();
    
    // Mark as initialized
    window.blogSectionInitialized = true;
    
    // Media type selection handler using event delegation
    document.addEventListener('change', function(e) {
        if (e.target.name === 'media_type') {
            const form = e.target.closest('form');
            const mediaInputs = form.querySelectorAll('.media-input');
            
            mediaInputs.forEach(input => input.style.display = 'none');
            
            if (e.target.value === 'youtube') {
                form.querySelector('.youtube-input').style.display = 'block';
            } else if (e.target.value === 'video' || e.target.value === 'image') {
                form.querySelector('.file-input').style.display = 'block';
            }
        }
    });
    
    // Add blog post submission handler
    document.getElementById('saveBlogBtn')?.addEventListener('click', async function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if (this.disabled) return;
        
        // Disable button
        this.disabled = true;
        
        try {
            const form = document.getElementById('addBlogForm');
            const formData = new FormData(form);
            formData.append('action', 'add');

            const response = await fetch('handler/blog_handler.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addBlogModal'));
                modal.hide();
                loadBlogPosts();
                form.reset();
                showAlert('Success', 'Blog post added successfully', 'success');
            } else {
                throw new Error(data.message || 'Failed to add blog post');
            }
        } catch (error) {
            console.error('Error saving blog post:', error);
            showAlert('Error', error.message || 'Failed to add blog post', 'error');
        } finally {
            this.disabled = false;
        }
    });
    
    // Update blog post handler
    document.getElementById('updateBlogBtn')?.addEventListener('click', async function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if (this.disabled) return;
        
        // Disable button
        this.disabled = true;
        
        try {
            const form = document.getElementById('editBlogForm');
            const formData = new FormData(form);
            formData.append('action', 'update');

            const response = await fetch('handler/blog_handler.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editBlogModal'));
                modal.hide();
                loadBlogPosts();
                showAlert('Success', 'Blog post updated successfully', 'success');
            } else {
                throw new Error(data.message || 'Failed to update blog post');
            }
        } catch (error) {
            console.error('Error updating blog post:', error);
            showAlert('Error', error.message || 'Failed to update blog post', 'error');
        } finally {
            this.disabled = false;
        }
    });
    
    // Initialize blog post actions using event delegation
    document.addEventListener('click', function(e) {
        // Edit blog post
        if (e.target.closest('.edit-post')) {
            const button = e.target.closest('.edit-post');
            const postId = button.getAttribute('data-id') || button.parentElement.getAttribute('data-id');
            editBlogPost(postId);
        }
        
        // Delete blog post
        if (e.target.closest('.delete-post')) {
            const button = e.target.closest('.delete-post');
            const postId = button.getAttribute('data-id') || button.parentElement.getAttribute('data-id');
            deleteBlogPost(postId);
        }
        
        // Play video
        if (e.target.closest('.play-button')) {
            const button = e.target.closest('.play-button');
            if (button.previousElementSibling && button.previousElementSibling.tagName === 'VIDEO') {
                playVideo(button);
            }
        }
    });
}

// Supporting functions

function loadBlogPosts() {
    const blogGrid = document.querySelector('.blog-posts-grid');
    if (!blogGrid) return;
    
    fetch('handler/blog_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=fetch'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            blogGrid.innerHTML = data.posts.map(post => createBlogPostCard(post)).join('');
        }
    })
    .catch(error => console.error('Error loading blog posts:', error));
}

function createBlogPostCard(post) {
    let mediaPreview = '';
    
    if (post.media_type === 'youtube') {
        const videoId = extractYouTubeId(post.media_url);
        mediaPreview = `
            <div class="youtube-preview">
                <img src="https://img.youtube.com/vi/${videoId}/mqdefault.jpg" alt="YouTube thumbnail">
                <a href="${post.media_url}" target="_blank" class="play-button" style="text-decoration: none;">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>`;
    } else if (post.media_type === 'video') {
        mediaPreview = `
            <div class="video-preview">
                <video src="uploads/blog/${post.media_url}" muted></video>
                <button class="play-button" onclick="playVideo(this)">
                    <i class="fas fa-play"></i>
                </button>
            </div>`;
    } else if (post.media_type === 'image') {
        mediaPreview = `
            <div class="image-preview">
                <img src="uploads/blog/${post.media_url}" alt="${post.title}">
            </div>`;
    }

    return `
        <div class="blog-post-card" data-id="${post.id}">
            <div class="media-preview">
                ${mediaPreview}
            </div>
            <div class="post-content">
                <h3>${post.title}</h3>
                <p>${post.content.substring(0, 100)}...</p>
                <div class="post-meta">
                    <span class="date">
                        <i class="far fa-clock"></i>
                        ${formatDate(post.created_at)}
                    </span>
                    <div class="post-actions">
                        <button class="btn btn-sm btn-primary edit-post" 
                                data-id="${post.id}" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editBlogModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-post" 
                                data-id="${post.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
}

function editBlogPost(postId) {
    fetch('handler/blog_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=get&id=${postId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const form = document.getElementById('editBlogForm');
            const post = data.post;
            
            form.querySelector('[name="post_id"]').value = post.id;
            form.querySelector('[name="title"]').value = post.title;
            form.querySelector('[name="content"]').value = post.content;
            form.querySelector('[name="external_link"]').value = post.external_link || '';
            
            // Handle media type selection
            const mediaTypeSelect = form.querySelector('[name="media_type"]');
            mediaTypeSelect.value = post.media_type;
            
            // Trigger change event to show appropriate inputs
            const changeEvent = new Event('change');
            mediaTypeSelect.dispatchEvent(changeEvent);
            
            // Populate YouTube URL if applicable
            if (post.media_type === 'youtube') {
                form.querySelector('[name="youtube_url"]').value = post.media_url;
            }
            
            // If there's existing media, show a preview
            if (post.media_url) {
                const currentMediaPreview = form.querySelector('.current-media-preview');
                if (currentMediaPreview) {
                    if (post.media_type === 'image') {
                        currentMediaPreview.innerHTML = `
                            <div class="mt-2">
                                <p>Current image:</p>
                                <img src="uploads/blog/${post.media_url}" alt="Current image" style="max-width: 200px; max-height: 100px;">
                            </div>`;
                    } else if (post.media_type === 'video') {
                        currentMediaPreview.innerHTML = `
                            <div class="mt-2">
                                <p>Current video:</p>
                                <video src="uploads/blog/${post.media_url}" style="max-width: 200px; max-height: 100px;" controls></video>
                            </div>`;
                    }
                }
            }
        }
    })
    .catch(error => {
        console.error('Error fetching blog post:', error);
        showAlert('Error', 'Failed to load blog post details', 'error');
    });
}

function deleteBlogPost(postId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This blog post will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('handler/blog_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadBlogPosts();
                    showAlert('Success', 'Blog post deleted successfully', 'success');
                } else {
                    showAlert('Error', data.message || 'Failed to delete blog post', 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting blog post:', error);
                showAlert('Error', 'Failed to delete blog post', 'error');
            });
        }
    });
}

function extractYouTubeId(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function playVideo(button) {
    const video = button.previousElementSibling;
    if (video.paused) {
        video.play();
        button.innerHTML = '<i class="fas fa-pause"></i>';
    } else {
        video.pause();
        button.innerHTML = '<i class="fas fa-play"></i>';
    }
}