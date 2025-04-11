document.addEventListener('DOMContentLoaded', function() {
    let messageModal = null;

    // Initialize modal
    const initMessageModal = () => {
        const modalElement = document.getElementById('messageModal');
        if (modalElement) {
            messageModal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: false
            });
        }
    };

    // Complete application handler
    document.querySelectorAll('.complete-application').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.id;
            const userId = this.dataset.userId;
            
            console.log('Application ID:', applicationId); // Debug log
            console.log('User ID:', userId); // Debug log
            
            Swal.fire({
                title: 'Complete Application?',
                html: 'Would you like to send a message to the client?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, with message',
                cancelButtonText: 'Just complete',
                showDenyButton: true,
                denyButtonText: 'Cancel'
            }).then((result) => {
                if (result.isDenied) {
                    return;
                }
                
                if (result.isConfirmed) {
                    try {
                        // Show message modal
                        document.getElementById('message_application_id').value = applicationId;
                        document.getElementById('message_user_id').value = userId;
                        document.getElementById('message_content').value = '';
                        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                        messageModal.show();
                    } catch (error) {
                        console.error('Error showing modal:', error); // Debug log
                    }
                } else {
                    updateApplicationStatus(applicationId, 'completed');
                }
            });
        });
    });

    // Cancel Application
    document.querySelectorAll('.cancel-application').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.id;
            
            Swal.fire({
                title: 'Cancel Application?',
                text: "This action cannot be reverted once done!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateApplicationStatus(applicationId, 'cancelled');
                }
            });
        });
    });

    // Pend Application
    document.querySelectorAll('.pend-application').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.id;
            
            Swal.fire({
                title: 'Set to Pending?',
                text: "This will change the application status to pending",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, set to pending'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateApplicationStatus(applicationId, 'pending');
                }
            });
        });
    });

    // Send message handler
    document.getElementById('sendMessageBtn')?.addEventListener('click', function() {
        try {
            const applicationId = document.getElementById('message_application_id').value;
            const userId = document.getElementById('message_user_id').value;
            const message = document.getElementById('message_content').value;

            if (!message.trim()) {
                Swal.fire('Error', 'Please enter a message', 'error');
                return;
            }

            fetch('handler/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include', // Changed from same-origin to include
                body: JSON.stringify({
                    application_id: applicationId,
                    user_id: userId,
                    message: message.trim()
                })
            })
            .then(async response => {
                const text = await response.text();
                console.log('Server response:', text); // Debug log
                
                try {
                    const data = JSON.parse(text);
                    if (response.status === 401) {
                        // Handle unauthorized access
                        Swal.fire({
                            icon: 'error',
                            title: 'Session Expired',
                            text: 'Please log in again',
                            confirmButtonText: 'Login'
                        }).then(() => {
                            window.location.href = 'admin-login.php';
                        });
                        return;
                    }
                    return data;
                } catch (e) {
                    console.error('Response parsing error:', e);
                    throw new Error('Invalid server response');
                }
            })
            .then(data => {
                if (data.success) {
                    const messageModal = bootstrap.Modal.getInstance(document.getElementById('messageModal'));
                    messageModal?.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Message sent successfully',
                        timer: 1500
                    }).then(() => {
                        updateApplicationStatus(applicationId, 'completed');
                    });
                } else {
                    throw new Error(data.message || 'Failed to send message');
                }
            })
            .catch(error => {
                console.error('Error details:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message
                });
            });
        } catch (error) {
            console.error('General error:', error);
            Swal.fire('Error', 'An unexpected error occurred', 'error');
        }
    });

    // Function to update application status
    function updateApplicationStatus(applicationId, status) {
        console.log('Updating status:', { applicationId, status });
        
        fetch('handler/update_application_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                id: applicationId,
                status: status
            })
        })
        .then(async response => {
            const text = await response.text();
            console.log('Server response:', text); // Debug log
            
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Response parsing error:', e);
                console.error('Raw response:', text);
                throw new Error('Invalid server response');
            }
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Updated!',
                    text: `Application has been ${status}`,
                    icon: 'success',
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(data.message || 'Failed to update status');
            }
        })
        .catch(error => {
            console.error('Update status error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message
            });
        });
    }

    // Initialize modal when DOM is ready
    initMessageModal();
});

