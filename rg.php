<?php
    require 'connect.php';
    session_start();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="CSS/rg.css?v=<?php echo time(); ?>">
    <style>
        /* Ensure styles are applied */
        body {
            background: #ffffff !important;
            overflow-y: auto !important;
        }
        
        /* Error message animation */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Email error styling */
        #email-error {
            animation: slideDown 0.3s ease;
        }
        
        /* Input error state */
        input.error, select.error {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="form-title">Online Application System</h2>
        <p class="form-subtitle">Create your account to get started</p>
        
        <form id="registerForm" action="register.php" method="POST">
            <div class="form-group">
                <label><span class="required">*</span>User Type</label>
                <div class="input-wrapper">
                    <select name="user_type" id="user_type" required onchange="toggleOfficerFields()">
                        <option value="student">Student</option>
                        <option value="officer">Admission Officer</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label><span class="required">*</span>Username</label>
                <div class="input-wrapper">
                    <input type="text" name="username" id="username" placeholder="Enter your username" required>
                </div>
            </div>

            <div class="form-group">
                <label><span class="required">*</span>Email</label>
                <div class="input-wrapper">
                    <input type="email" name="email" id="email" placeholder="Enter your email" required onblur="validateEmail()">
                    <div id="email-error" class="error-message" style="display: none; color: #dc3545; font-size: 12px; margin-top: 5px;"></div>
                </div>
            </div>

            <div class="form-group">
                <label><span class="required">*</span>Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                </div>
            </div>

            <div class="form-group">
                <label><span class="required">*</span>Confirm Password</label>
                <div class="input-wrapper">
                    <input type="password" name="confirm" id="confirm" placeholder="Confirm your password" required>
                </div>
            </div>

            <!-- Officer-specific fields -->
            <div id="officerFields" class="officer-fields" style="display: none;">
                <div class="form-group">
                    <label><span class="required">*</span>Full Name</label>
                    <div class="input-wrapper">
                        <input type="text" name="real_name" id="real_name" placeholder="Enter your full name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><span class="required">*</span>Date of Birth</label>
                    <div class="input-wrapper">
                        <input type="date" name="date_of_birth" id="date_of_birth" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><span class="required">*</span>University</label>
                    <div class="input-wrapper">
                        <select name="university_id" id="university_id" class="modern-select" required>
                            <option value="">Select University</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn">Create Account</button>
        </form>

        <?php 
            if(isset($_SESSION['fail'])) {
                echo '<div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb; animation: slideDown 0.3s ease;">' . htmlspecialchars($_SESSION['fail']) . '</div>'; 
                unset($_SESSION['fail']);
            }
            if(isset($_SESSION['success'])) {
                echo '<div class="success-message" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb; animation: slideDown 0.3s ease;">' . htmlspecialchars($_SESSION['success']) . '</div>'; 
                unset($_SESSION['success']);
            }
        ?>
        
        <div class="register-link">
            Already have an account? <a href="login_.php">Login now</a>
        </div>
    </div>

    <script>
        let schools = [];

        // Load school list
        async function loadSchools() {
            try {
                const response = await fetch('api/get_public_schools.php');
                const result = await response.json();
                if (result.success) {
                    schools = result.data;
                    const select = document.getElementById('university_id');
                    select.innerHTML = '<option value="">Select University</option>';
                    schools.forEach(school => {
                        const option = document.createElement('option');
                        option.value = school.id;
                        option.textContent = school.name;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Failed to load school list:', error);
            }
        }

        // Toggle officer fields display
        function toggleOfficerFields() {
            const userType = document.getElementById('user_type').value;
            const officerFields = document.getElementById('officerFields');
            const realNameInput = document.getElementById('real_name');
            const dateOfBirthInput = document.getElementById('date_of_birth');
            const universitySelect = document.getElementById('university_id');
            
            if (userType === 'officer') {
                officerFields.style.display = 'block';
                realNameInput.setAttribute('required', 'required');
                dateOfBirthInput.setAttribute('required', 'required');
                universitySelect.setAttribute('required', 'required');
                loadSchools();
            } else {
                officerFields.style.display = 'none';
                realNameInput.removeAttribute('required');
                dateOfBirthInput.removeAttribute('required');
                universitySelect.removeAttribute('required');
            }
        }

        // Email validation function (matches database trigger: %_@__%.__%)
        function validateEmail() {
            const emailInput = document.getElementById('email');
            const errorDiv = document.getElementById('email-error');
            const email = emailInput.value.trim();
            
            // Clear previous error
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';
            emailInput.style.borderColor = '';
            
            if (!email) {
                return true; // Let HTML5 required validation handle empty email
            }
            
            // Database trigger pattern: at least one char + @ + at least two chars + . + at least two chars
            // Pattern: .+@.+\..{2,}
            const emailPattern = /^.+@.+\..{2,}$/;
            
            if (!emailPattern.test(email)) {
                errorDiv.textContent = 'Please enter a valid email address (e.g., user@example.com)';
                errorDiv.style.display = 'block';
                emailInput.style.borderColor = '#dc3545';
                return false;
            }
            
            return true;
        }
        
        // Form submission validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            // Validate email before submission
            if (!validateEmail()) {
                e.preventDefault();
                e.stopPropagation();
                
                // Scroll to email field
                document.getElementById('email').scrollIntoView({ behavior: 'smooth', block: 'center' });
                document.getElementById('email').focus();
                
                return false;
            }
            
            // Additional validation for officer fields if needed
            const userType = document.getElementById('user_type').value;
            if (userType === 'officer') {
                const realName = document.getElementById('real_name').value.trim();
                const universityId = document.getElementById('university_id').value;
                
                if (!realName) {
                    e.preventDefault();
                    showError('Please enter your full name');
                    document.getElementById('real_name').focus();
                    return false;
                }
                
                if (!universityId || universityId === '') {
                    e.preventDefault();
                    showError('Please select a university');
                    document.getElementById('university_id').focus();
                    return false;
                }
            }
            
            return true;
        });
        
        // Show error message function
        function showError(message) {
            // Remove existing error messages
            const existingErrors = document.querySelectorAll('.form-error-message');
            existingErrors.forEach(err => err.remove());
            
            // Create and show error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'form-error-message';
            errorDiv.style.cssText = 'background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;';
            errorDiv.textContent = message;
            
            const form = document.getElementById('registerForm');
            form.insertBefore(errorDiv, form.firstChild);
            
            // Scroll to error
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Remove error after 5 seconds
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }
        
        // Real-time email validation on input
        document.getElementById('email').addEventListener('input', function() {
            const email = this.value.trim();
            const errorDiv = document.getElementById('email-error');
            
            // Only show error if user has started typing and left the field
            if (email && this === document.activeElement) {
                // Clear error while typing
                errorDiv.style.display = 'none';
                this.style.borderColor = '';
            }
        });

        // Initialize on page load
        window.addEventListener('DOMContentLoaded', function() {
            toggleOfficerFields();
        });
    </script>
</body>
</html>