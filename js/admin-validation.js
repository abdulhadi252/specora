// ================================================
// js/admin-validation.js — Specora
// Client-side validation for admin forms (login, register, profile)
// ================================================

(function() {
    'use strict';

    // ═══════════════════════════════════════════════
    // ADMIN LOGIN FORM VALIDATION
    // ═══════════════════════════════════════════════
    var loginForm = document.querySelector('form[action=""][method="POST"]');
    if (loginForm && document.getElementById('email') && document.getElementById('password')) {
        var emailInput = document.getElementById('email');
        var passwordInput = document.getElementById('password');
        
        var emailError = document.querySelector('.field-err[data-field="email"]') || document.createElement('div');
        var passwordError = document.querySelector('.field-err[data-field="password"]') || document.createElement('div');
        
        function validateLoginEmail() {
            var val = emailInput.value.trim();
            var emailField = emailInput.closest('.field');
            if (!val) {
                emailInput.classList.add('input-err');
                showFieldError(emailField, 'Email is required');
                return false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                emailInput.classList.add('input-err');
                showFieldError(emailField, 'Please enter a valid email address');
                return false;
            } else {
                emailInput.classList.remove('input-err');
                clearFieldError(emailField);
                return true;
            }
        }
        
        function validateLoginPassword() {
            var val = passwordInput.value.trim();
            var passField = passwordInput.closest('.field');
            if (!val) {
                passwordInput.classList.add('input-err');
                showFieldError(passField, 'Password is required');
                return false;
            } else {
                passwordInput.classList.remove('input-err');
                clearFieldError(passField);
                return true;
            }
        }
        
        // Real-time validation
        emailInput.addEventListener('blur', validateLoginEmail);
        emailInput.addEventListener('input', function() {
            if (this.classList.contains('input-err')) validateLoginEmail();
        });
        passwordInput.addEventListener('blur', validateLoginPassword);
        passwordInput.addEventListener('input', function() {
            if (this.classList.contains('input-err')) validateLoginPassword();
        });
        
        // Form submit validation
        loginForm.addEventListener('submit', function(e) {
            var validEmail = validateLoginEmail();
            var validPass = validateLoginPassword();
            if (!validEmail || !validPass) {
                e.preventDefault();
                var firstErr = loginForm.querySelector('.input-err');
                if (firstErr) firstErr.focus({ preventScroll: true });
            }
        });
    }

    // ═══════════════════════════════════════════════
    // ADMIN REGISTER FORM VALIDATION
    // ═══════════════════════════════════════════════
    var registerForm = null;
    var regFields = {};
    
    // Find the register form (it has username, email, password, confirm_password fields)
    var allForms = document.querySelectorAll('form[method="POST"]');
    allForms.forEach(function(f) {
        if (f.querySelector('#username') && f.querySelector('#confirm_password')) {
            registerForm = f;
        }
    });
    
    if (registerForm) {
        regFields = {
            username: registerForm.querySelector('#username'),
            email: registerForm.querySelector('#email'),
            password: registerForm.querySelector('#password'),
            confirmPassword: registerForm.querySelector('#confirm_password')
        };
        
        function validateRegUsername() {
            var val = regFields.username.value.trim();
            var field = regFields.username.closest('.field');
            if (!val) {
                regFields.username.classList.add('input-err');
                showFieldError(field, 'Username is required');
                return false;
            } else if (val.length < 3) {
                regFields.username.classList.add('input-err');
                showFieldError(field, 'Username must be at least 3 characters');
                return false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(val)) {
                regFields.username.classList.add('input-err');
                showFieldError(field, 'Username can only contain letters, numbers, and underscores');
                return false;
            } else {
                regFields.username.classList.remove('input-err');
                clearFieldError(field);
                return true;
            }
        }
        
        function validateRegEmail() {
            var val = regFields.email.value.trim();
            var field = regFields.email.closest('.field');
            if (!val) {
                regFields.email.classList.add('input-err');
                showFieldError(field, 'Email is required');
                return false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                regFields.email.classList.add('input-err');
                showFieldError(field, 'Please enter a valid email address');
                return false;
            } else {
                regFields.email.classList.remove('input-err');
                clearFieldError(field);
                return true;
            }
        }
        
        function validateRegPassword() {
            var val = regFields.password.value;
            var field = regFields.password.closest('.field');
            if (!val) {
                regFields.password.classList.add('input-err');
                showFieldError(field, 'Password is required');
                return false;
            } else if (val.length < 8) {
                regFields.password.classList.add('input-err');
                showFieldError(field, 'Password must be at least 8 characters');
                return false;
            } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?])/.test(val)) {
                regFields.password.classList.add('input-err');
                showFieldError(field, 'Must contain: uppercase, lowercase, number, and special character');
                return false;
            } else {
                regFields.password.classList.remove('input-err');
                clearFieldError(field);
                return true;
            }
        }
        
        function validateRegConfirmPassword() {
            var val = regFields.confirmPassword.value;
            var field = regFields.confirmPassword.closest('.field');
            if (!val) {
                regFields.confirmPassword.classList.add('input-err');
                showFieldError(field, 'Please confirm your password');
                return false;
            } else if (val !== regFields.password.value) {
                regFields.confirmPassword.classList.add('input-err');
                showFieldError(field, 'Passwords do not match');
                return false;
            } else {
                regFields.confirmPassword.classList.remove('input-err');
                clearFieldError(field);
                return true;
            }
        }
        
        // Real-time validation
        regFields.username.addEventListener('blur', validateRegUsername);
        regFields.username.addEventListener('input', function() {
            if (this.classList.contains('input-err')) validateRegUsername();
        });
        regFields.email.addEventListener('blur', validateRegEmail);
        regFields.email.addEventListener('input', function() {
            if (this.classList.contains('input-err')) validateRegEmail();
        });
        regFields.password.addEventListener('blur', validateRegPassword);
        regFields.password.addEventListener('input', function() {
            if (this.classList.contains('input-err')) validateRegPassword();
        });
        regFields.confirmPassword.addEventListener('blur', validateRegConfirmPassword);
        regFields.confirmPassword.addEventListener('input', function() {
            if (this.classList.contains('input-err')) validateRegConfirmPassword();
        });
        
        // Form submit validation
        registerForm.addEventListener('submit', function(e) {
            var v1 = validateRegUsername();
            var v2 = validateRegEmail();
            var v3 = validateRegPassword();
            var v4 = validateRegConfirmPassword();
            if (!v1 || !v2 || !v3 || !v4) {
                e.preventDefault();
                var firstErr = registerForm.querySelector('.input-err');
                if (firstErr) firstErr.focus({ preventScroll: true });
            }
        });
    }

    // ═══════════════════════════════════════════════
    // ADMIN PROFILE FORM VALIDATION
    // ═══════════════════════════════════════════════
    var profileForm = document.querySelector('form[action=""][method="POST"]');
    if (profileForm && document.querySelector('.form-divider-label')) {
        var profUsername = document.getElementById('username');
        var profEmail = document.getElementById('email');
        var profCurPass = document.getElementById('current_password');
        var profNewPass = document.getElementById('new_password');
        var profConfirmPass = document.getElementById('confirm_password');
        
        if (profUsername && profEmail) {
            function validateProfUsername() {
                var val = profUsername.value.trim();
                var field = profUsername.closest('.field');
                if (!val) {
                    profUsername.classList.add('input-err');
                    showFieldError(field, 'Username is required');
                    return false;
                } else if (val.length < 3) {
                    profUsername.classList.add('input-err');
                    showFieldError(field, 'Username must be at least 3 characters');
                    return false;
                } else if (!/^[a-zA-Z0-9_]+$/.test(val)) {
                    profUsername.classList.add('input-err');
                    showFieldError(field, 'Username can only contain letters, numbers, and underscores');
                    return false;
                } else {
                    profUsername.classList.remove('input-err');
                    clearFieldError(field);
                    return true;
                }
            }
            
            function validateProfEmail() {
                var val = profEmail.value.trim();
                var field = profEmail.closest('.field');
                if (!val) {
                    profEmail.classList.add('input-err');
                    showFieldError(field, 'Email is required');
                    return false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                    profEmail.classList.add('input-err');
                    showFieldError(field, 'Please enter a valid email address');
                    return false;
                } else {
                    profEmail.classList.remove('input-err');
                    clearFieldError(field);
                    return true;
                }
            }
            
            // Real-time validation
            profUsername.addEventListener('blur', validateProfUsername);
            profUsername.addEventListener('input', function() {
                if (this.classList.contains('input-err')) validateProfUsername();
            });
            profEmail.addEventListener('blur', validateProfEmail);
            profEmail.addEventListener('input', function() {
                if (this.classList.contains('input-err')) validateProfEmail();
            });
            
            // Password fields validation (if they exist)
            if (profNewPass && profConfirmPass) {
                function validateProfNewPassword() {
                    var val = profNewPass.value;
                    var field = profNewPass.closest('.field');
                    if (val && val.length < 8) {
                        profNewPass.classList.add('input-err');
                        showFieldError(field, 'Password must be at least 8 characters');
                        return false;
                    } else if (val && !/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?])/.test(val)) {
                        profNewPass.classList.add('input-err');
                        showFieldError(field, 'Must contain: uppercase, lowercase, number, and special character');
                        return false;
                    } else {
                        profNewPass.classList.remove('input-err');
                        clearFieldError(field);
                        return true;
                    }
                }
                
                function validateProfConfirmPassword() {
                    var val = profConfirmPass.value;
                    var field = profConfirmPass.closest('.field');
                    if (profNewPass.value && !val) {
                        profConfirmPass.classList.add('input-err');
                        showFieldError(field, 'Please confirm your password');
                        return false;
                    } else if (profNewPass.value && val !== profNewPass.value) {
                        profConfirmPass.classList.add('input-err');
                        showFieldError(field, 'Passwords do not match');
                        return false;
                    } else {
                        profConfirmPass.classList.remove('input-err');
                        clearFieldError(field);
                        return true;
                    }
                }
                
                profNewPass.addEventListener('blur', validateProfNewPassword);
                profNewPass.addEventListener('input', function() {
                    if (this.classList.contains('input-err')) validateProfNewPassword();
                });
                profConfirmPass.addEventListener('blur', validateProfConfirmPassword);
                profConfirmPass.addEventListener('input', function() {
                    if (this.classList.contains('input-err')) validateProfConfirmPassword();
                });
            }
            
            // Form submit validation
            profileForm.addEventListener('submit', function(e) {
                var v1 = validateProfUsername();
                var v2 = validateProfEmail();
                var v3 = true, v4 = true;
                if (profNewPass && profNewPass.value) {
                    v3 = validateProfNewPassword();
                    v4 = validateProfConfirmPassword();
                }
                if (!v1 || !v2 || !v3 || !v4) {
                    e.preventDefault();
                    var firstErr = profileForm.querySelector('.input-err');
                    if (firstErr) firstErr.focus({ preventScroll: true });
                }
            });
        }
    }

    // ═══════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════

    function showFieldError(fieldEl, message) {
        if (!fieldEl) return;
        var existing = fieldEl.querySelector('.field-err');
        if (existing) {
            existing.textContent = message;
            return;
        }
        var err = document.createElement('div');
        err.className = 'field-err';
        err.textContent = message;
        fieldEl.appendChild(err);
    }

    function clearFieldError(fieldEl) {
        if (!fieldEl) return;
        var existing = fieldEl.querySelector('.field-err');
        if (existing) existing.remove();
    }

})();
