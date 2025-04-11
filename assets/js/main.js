// Example: Simple form validation for registration
document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.querySelector('form[action="register.php"]');
    if(registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            if(password !== confirmPassword) {
                e.preventDefault();
                alert("Passwords do not match.");
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const currentPage = parseInt(this.dataset.page);
            const totalPosts = parseInt(this.dataset.total);
            const postsPerPage = 5;
            
            this.classList.add('loading');
            this.innerHTML = 'Loading... <i class="fas fa-spinner fa-spin"></i>';
            
            const formData = new FormData();
            formData.append('page', currentPage + 1);
            
            fetch('handler/load_more_posts.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const blogGrid = document.getElementById('blogGrid');
                    const existingPosts = blogGrid.children.length;
                    
                    // Add new posts
                    blogGrid.insertAdjacentHTML('beforeend', data.html);
                    
                    // Get the first newly added post
                    const firstNewPost = blogGrid.children[existingPosts];
                    
                    // Smooth scroll to the first new post
                    if (firstNewPost) {
                        firstNewPost.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                    
                    this.dataset.page = currentPage + 1;
                    
                    const loadedPosts = (currentPage + 1) * postsPerPage;
                    if (loadedPosts >= totalPosts || !data.has_more) {
                        this.classList.add('disabled');
                        this.innerHTML = 'No More Posts';
                        this.disabled = true;
                    } else {
                        this.classList.remove('loading');
                        this.innerHTML = 'Load More <i class="fas fa-chevron-down"></i>';
                    }
                } else {
                    throw new Error(data.message || 'Error loading posts');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.classList.remove('loading');
                this.innerHTML = 'Error Loading Posts';
            });
        });
    }
});
