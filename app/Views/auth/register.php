<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PetPal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        }
    
    </style>
</head>

<body>
    <div class="register-container">
        <!-- Left Side - Branding -->
        <div class="register-left">
            <div class="register-left-content">
                <div class="logo">
                    <i class="fas fa-paw"></i>
                </div>
                <h1 class="brand-title">Join PetPal</h1>
                <p class="brand-subtitle">
                    Create your account and start managing your pet's needs with our comprehensive platform.
                </p>
                
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-heart"></i>
                        <span>Track pet health & wellness</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-calendar"></i>
                        <span>Schedule appointments</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        <span>Connect with pet community</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Registration Form -->
        <div class="register-right">
            <div class="register-header">
                <h2 class="register-title">Create Account</h2>
                <p class="register-subtitle">Fill in your details to get started</p>
            </div>

            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger flash-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success flash-message">
                    <i class="fas fa-check-circle"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('register') ?>" method="post" id="registerForm">
                <?= csrf_field() ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name</label>
                        <div style="position: relative;">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" class="form-control" name="first_name" id="first_name"
                                   placeholder="Enter first name" value="<?= set_value('first_name') ?>" required>
                        </div>
                        <div class="validation-feedback" id="first_name_feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name</label>
                        <div style="position: relative;">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" class="form-control" name="last_name" id="last_name"
                                   placeholder="Enter last name" value="<?= set_value('last_name') ?>" required>
                        </div>
                        <div class="validation-feedback" id="last_name_feedback"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div style="position: relative;">
                        <i class="fas fa-at input-icon"></i>
                        <input type="text" class="form-control" name="username" id="username"
                               placeholder="Choose a username" value="<?= set_value('username') ?>" required>
                    </div>
                    <div class="validation-feedback" id="username_feedback"></div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div style="position: relative;">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" class="form-control" name="email" id="email"
                               placeholder="Enter your email" value="<?= set_value('email') ?>" required>
                    </div>
                    <div class="validation-feedback" id="email_feedback"></div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div style="position: relative;">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" name="password" id="password"
                               placeholder="Create a password" required>
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span id="strengthText" class="text-muted">Password strength</span>
                    </div>
                    <div class="validation-feedback" id="password_feedback"></div>
                </div>

                <div class="form-group">
                    <label for="pass_confirm" class="form-label">Confirm Password</label>
                    <div style="position: relative;">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" name="pass_confirm" id="pass_confirm"
                               placeholder="Confirm your password" required>
                        <button type="button" class="password-toggle" id="confirmPasswordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="validation-feedback" id="confirm_password_feedback"></div>
                </div>

                <button type="submit" class="btn btn-register" id="registerBtn">
                    <span class="btn-text">Create Account</span>
                </button>
            </form>

            <div class="login-link">
                <p>Already have an account? 
                    <a href="<?= base_url('login'); ?>">Sign in here</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password Toggle Functionality
        function setupPasswordToggle(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            const icon = toggle.querySelector('i');

            toggle.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                
                if (type === 'text') {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }

        setupPasswordToggle('passwordToggle', 'password');
        setupPasswordToggle('confirmPasswordToggle', 'pass_confirm');

        // Password Strength Checker
        const passwordInput = document.getElementById('password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        function checkPasswordStrength(password) {
            let strength = 0;
            let feedback = [];

            if (password.length >= 8) strength += 25;
            else feedback.push('At least 8 characters');

            if (/[a-z]/.test(password)) strength += 25;
            else feedback.push('Lowercase letter');

            if (/[A-Z]/.test(password)) strength += 25;
            else feedback.push('Uppercase letter');

            if (/[0-9]/.test(password)) strength += 25;
            else feedback.push('Number');

            return { strength, feedback };
        }

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const result = checkPasswordStrength(password);
            
            strengthFill.className = 'strength-fill';
            
            if (result.strength <= 25) {
                strengthFill.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
                strengthText.style.color = 'var(--danger-color)';
            } else if (result.strength <= 50) {
                strengthFill.classList.add('strength-fair');
                strengthText.textContent = 'Fair password';
                strengthText.style.color = 'var(--warning-color)';
            } else if (result.strength <= 75) {
                strengthFill.classList.add('strength-good');
                strengthText.textContent = 'Good password';
                strengthText.style.color = '#3b82f6';
            } else {
                strengthFill.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
                strengthText.style.color = 'var(--success-color)';
            }
        });

        // Form Validation
        const form = document.getElementById('registerForm');
        const inputs = document.querySelectorAll('.form-control');

        function validateField(field) {
            const value = field.value.trim();
            const feedback = document.getElementById(field.id + '_feedback');
            let isValid = true;
            let message = '';

            switch (field.id) {
                case 'first_name':
                case 'last_name':
                    if (value.length < 2) {
                        isValid = false;
                        message = 'Must be at least 2 characters';
                    }
                    break;
                case 'username':
                    if (value.length < 3) {
                        isValid = false;
                        message = 'Username must be at least 3 characters';
                    } else if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                        isValid = false;
                        message = 'Only letters, numbers, and underscores allowed';
                    }
                    break;
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        isValid = false;
                        message = 'Please enter a valid email address';
                    }
                    break;
                case 'password':
                    const result = checkPasswordStrength(value);
                    if (result.strength < 50) {
                        isValid = false;
                        message = 'Password needs: ' + result.feedback.join(', ');
                    }
                    break;
                case 'pass_confirm':
                    const password = document.getElementById('password').value;
                    if (value !== password) {
                        isValid = false;
                        message = 'Passwords do not match';
                    }
                    break;
            }

            field.classList.remove('is-valid', 'is-invalid');
            feedback.classList.remove('valid', 'invalid');

            if (value && field.checkValidity()) {
                if (isValid) {
                    field.classList.add('is-valid');
                    feedback.classList.add('valid');
                    feedback.textContent = '✓ Looks good!';
                } else {
                    field.classList.add('is-invalid');
                    feedback.classList.add('invalid');
                    feedback.textContent = message;
                }
            }

            return isValid;
        }

        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('is-invalid') || input.classList.contains('is-valid')) {
                    validateField(input);
                }
            });
        });

        // Form Submission
        const registerBtn = document.getElementById('registerBtn');
        const btnText = document.querySelector('.btn-text');

        form.addEventListener('submit', function(e) {
            let isFormValid = true;
            
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isFormValid = false;
                }
            });

            if (!isFormValid) {
                e.preventDefault();
                return;
            }

            // Add loading state
            registerBtn.classList.add('loading');
            btnText.textContent = 'Creating Account...';
            registerBtn.disabled = true;
        });

        // Auto-hide flash messages
        setTimeout(function () {
            const alerts = document.querySelectorAll('.flash-message');
            alerts.forEach(function (el) {
                el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-20px)';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);

        // Keyboard Navigation Enhancement
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName === 'INPUT') {
                const inputs = Array.from(document.querySelectorAll('input[required]'));
                const currentIndex = inputs.indexOf(document.activeElement);
                
                if (currentIndex < inputs.length - 1) {
                    e.preventDefault();
                    inputs[currentIndex + 1].focus();
                }
            }
        });
    </script>
</body>

</html>