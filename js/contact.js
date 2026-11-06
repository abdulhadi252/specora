// ================================================
// js/contact.js — Specora
// Contact page JavaScript — form validation, AJAX submit, star rating, photo upload
// ================================================

// ═══════════════════════════════════════════════
// CONTACT FORM — Validation & Submission
// ═══════════════════════════════════════════════

// ── Real-time validation helpers ──
function validateContactField(fieldId, groupId, errorId, rules) {
    var field = document.getElementById(fieldId);
    var group = document.getElementById(groupId);
    var errorEl = document.getElementById(errorId);
    if (!field || !group) return true;
    
    var value = field.value.trim();
    var isValid = true;
    var errorMsg = '';
    
    if (rules.required && !value) {
        isValid = false;
        errorMsg = rules.requiredMsg || 'This field is required';
    } else if (rules.email && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        isValid = false;
        errorMsg = rules.emailMsg || 'Please enter a valid email';
    } else if (rules.minLength && value.length < rules.minLength) {
        isValid = false;
        errorMsg = rules.minLengthMsg || 'Too short';
    }
    
    group.classList.toggle('error', !isValid);
    if (errorEl) errorEl.textContent = errorMsg;
    return isValid;
}

// ── Set up real-time validation on all contact form fields ──
(function() {
    function setupContactValidation() {
        var fields = [
            { field: 'fldName', group: 'fgName', error: 'nameError', rules: { required: true, requiredMsg: 'Please enter your name', alpha: true, alphaMsg: 'Name can only contain letters and spaces' }},
            { field: 'fldEmail', group: 'fgEmail', error: 'emailError', rules: { required: true, email: true, requiredMsg: 'Email is required', emailMsg: 'Please enter a valid email address' }},
            { field: 'fldSubject', group: 'fgSubject', error: null, rules: { required: false }},
            { field: 'fldMessage', group: 'fgMessage', error: 'msgError', rules: { required: true, requiredMsg: 'Please write a message', minLength: 10, minLengthMsg: 'Message must be at least 10 characters' }}
        ];
        
        fields.forEach(function(f) {
            var el = document.getElementById(f.field);
            if (!el) return;
            
            // Validate on blur
            el.addEventListener('blur', function() {
                validateContactField(f.field, f.group, f.error, f.rules);
            });
            
            // Validate on input (clear error immediately when user types)
            el.addEventListener('input', function() {
                var group = document.getElementById(f.group);
                if (group && group.classList.contains('error')) {
                    validateContactField(f.field, f.group, f.error, f.rules);
                }
            });
        });
        
        // Category select validation
        var catSelect = document.getElementById('fldCategory');
        if (catSelect) {
            catSelect.addEventListener('change', function() {
                var group = this.closest('.form-group');
                if (group) {
                    group.classList.toggle('error', !this.value);
                }
            });
        }
    }
    setupContactValidation();
})();

function submitContactForm() {
    var name = document.getElementById('fldName').value.trim();
    var email = document.getElementById('fldEmail').value.trim();
    var subject = document.getElementById('fldSubject').value.trim();
    var category = document.getElementById('fldCategory').value;
    var message = document.getElementById('fldMessage').value.trim();
    var valid = true;
    
    // Validate all fields with real-time rules
    if (!validateContactField('fldName', 'fgName', 'nameError', { required: true, requiredMsg: 'Please enter your name', alpha: true, alphaMsg: 'Name can only contain letters and spaces' })) valid = false;
    if (!validateContactField('fldEmail', 'fgEmail', 'emailError', { required: true, email: true, requiredMsg: 'Email is required', emailMsg: 'Please enter a valid email address' })) valid = false;
    if (!validateContactField('fldMessage', 'fgMessage', 'msgError', { required: true, requiredMsg: 'Please write a message', minLength: 10, minLengthMsg: 'Message must be at least 10 characters' })) valid = false;
    
    // Category validation
    var catGroup = document.getElementById('fldCategory').closest('.form-group');
    if (!category) { catGroup.classList.add('error'); valid = false; }
    else { catGroup.classList.remove('error'); }
    
    if (!valid) {
        // Scroll to first error
        var firstError = document.querySelector('.form-group.error');
        if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    
    var btn = document.getElementById('submitBtn');
    btn.classList.add('loading');
    // Re-enable button after 30s timeout to prevent stuck loading state
    var loadTimer = setTimeout(function() {
        btn.classList.remove('loading');
        showFormAlert('Request timed out. Please check your connection and try again.', 'error');
    }, 30000);
    var formData = new URLSearchParams();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('subject', subject);
    formData.append('category', category);
    formData.append('message', message);
    fetch('../contact_process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(function(r) {
        if (!r.ok) {
            return r.json().then(function(errData) {
                throw { status: r.status, body: errData };
            }).catch(function(parseErr) {
                if (parseErr && parseErr.status) throw parseErr;
                throw { status: r.status, body: { message: 'Server returned status ' + r.status } };
            });
        }
        return r.json();
    })
    .then(function(data) {
        clearTimeout(loadTimer);
        btn.classList.remove('loading');
        if (data.success) {
            document.getElementById('liveForm').style.display = 'none';
            document.getElementById('successState').style.display = 'block';
        } else {
            if (data.errors) {
                if (data.errors.name) document.getElementById('fgName').classList.add('error');
                if (data.errors.email) document.getElementById('fgEmail').classList.add('error');
                if (data.errors.message) document.getElementById('fgMessage').classList.add('error');
            }
            showFormAlert(data.message || 'Something went wrong. Please try again.', 'error');
        }
    })
    .catch(function(err) {
        clearTimeout(loadTimer);
        btn.classList.remove('loading');
        var msg = 'Network error. Please check your connection and try again.';
        if (err && err.body && err.body.message) {
            msg = err.body.message;
        } else if (err && err.status) {
            msg = 'Server error (status ' + err.status + '). Please try again later.';
        }
        showFormAlert(msg, 'error');
    });
}

// ── CHARACTER COUNTER ──
(function() {
    var msgEl = document.getElementById('fldMessage');
    if (msgEl) {
        msgEl.addEventListener('input', function() {
            var len = this.value.length;
            var max = this.maxLength || 1000;
            document.getElementById('charCounter').textContent = len + ' / ' + max;
        });
    }
})();

// ── Category quick-select for contact form ──
(function() {
    document.querySelectorAll('.quick-cat').forEach(function(cat) {
        cat.addEventListener('click', function() {
            var text = this.querySelector('.quick-cat-text').textContent;
            var select = document.getElementById('fldCategory');
            if (select) {
                for (var i = 0; i < select.options.length; i++) {
                    if (select.options[i].value === text) {
                        select.value = text;
                        var evt = new Event('change', { bubbles: true });
                        select.dispatchEvent(evt);
                        document.querySelector('.contact-form-wrap').scrollIntoView({ behavior: 'smooth', block: 'center' });
                        break;
                    }
                }
            }
        });
    });
})();

// ═══════════════════════════════════════════════
// HELPER: Show form-level alert
// ═══════════════════════════════════════════════

function showFormAlert(message, type) {
    var existing = document.querySelector('.form-alert-top');
    if (existing) existing.remove();

    var alertEl = document.createElement('div');
    alertEl.className = 'form-alert-top';
    alertEl.setAttribute('role', 'alert');
    alertEl.style.cssText = 'display:flex;align-items:center;gap:10px;padding:14px 18px;border-radius:var(--radius-sm);margin-bottom:20px;font-size:13px;font-weight:500;line-height:1.5;animation:alertSlideIn .35s ease forwards;';

    if (type === 'error') {
        alertEl.style.background = '#FEF2F2';
        alertEl.style.border = '1px solid #FECACA';
        alertEl.style.color = '#991B1B';
        alertEl.innerHTML = '<span style="flex-shrink:0;font-size:16px;">&#9888;</span><span>' + escapeHtml(message) + '</span>';
    } else {
        alertEl.style.background = '#F0FDF4';
        alertEl.style.border = '1px solid #BBF7D0';
        alertEl.style.color = '#166534';
        alertEl.innerHTML = '<span style="flex-shrink:0;font-size:16px;">&#10003;</span><span>' + escapeHtml(message) + '</span>';
    }

    var form = document.getElementById('liveForm') || document.getElementById('liveFeedback');
    if (form) {
        form.parentNode.insertBefore(alertEl, form);
    } else {
        var wrap = document.querySelector('.contact-form-wrap');
        if (wrap) wrap.insertBefore(alertEl, wrap.querySelector('.form-sub').nextSibling);
    }

    setTimeout(function() {
        if (alertEl.parentNode) {
            alertEl.style.opacity = '0';
            alertEl.style.transform = 'translateY(-10px)';
            alertEl.style.transition = 'opacity .3s ease,transform .3s ease';
            setTimeout(function() { if (alertEl.parentNode) alertEl.remove(); }, 300);
        }
    }, 8000);
}

function escapeHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// ═══════════════════════════════════════════════
// TESTIMONIAL FORM — Validation & Submission
// ═══════════════════════════════════════════════

// ── Testimonial field validation ──
function validateTestimonialField(fieldId, rules) {
    var field = document.getElementById(fieldId);
    if (!field) return true;
    var group = field.closest('.tf-group');
    if (!group) return true;
    var errorEl = group.querySelector('.tf-error');
    var value = field.value.trim();
    var isValid = true;
    var errorMsg = '';
    
    if (rules.required && !value) {
        isValid = false;
        errorMsg = rules.requiredMsg || 'This field is required';
    } else if (rules.email && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        isValid = false;
        errorMsg = rules.emailMsg || 'Please enter a valid email';
    } else if (rules.minLength && value.length < rules.minLength) {
        isValid = false;
        errorMsg = rules.minLengthMsg || 'Too short';
    }
    
    group.classList.toggle('tf-error', !isValid);
    if (errorEl) errorEl.textContent = errorMsg;
    return isValid;
}

// ── Set up real-time validation on testimonial form fields ──
(function() {
    var tmFields = [
        { field: 'tmName', rules: { required: true, requiredMsg: 'Please enter your name', alpha: true, alphaMsg: 'Name can only contain letters and spaces' }},
        { field: 'tmEmail', rules: { required: false, email: true, emailMsg: 'Please enter a valid email' }},
        { field: 'tmCity', rules: { required: false }},
        { field: 'tmFeedback', rules: { required: true, requiredMsg: 'Please write your testimonial', minLength: 10, minLengthMsg: 'Testimonial must be at least 10 characters' }}
    ];
    
    tmFields.forEach(function(f) {
        var el = document.getElementById(f.field);
        if (!el) return;
        
        el.addEventListener('blur', function() {
            validateTestimonialField(f.field, f.rules);
        });
        
        el.addEventListener('input', function() {
            var group = this.closest('.tf-group');
            if (group && group.classList.contains('tf-error')) {
                validateTestimonialField(f.field, f.rules);
            }
        });
    });
})();

function submitTestimonial(event) {
    var name = document.getElementById('tmName').value.trim();
    var email = document.getElementById('tmEmail').value.trim();
    var city = document.getElementById('tmCity').value.trim();
    var feedback = document.getElementById('tmFeedback').value.trim();
    var valid = true;
    
    // Validate
    if (!validateTestimonialField('tmName', { required: true, requiredMsg: 'Please enter your name', alpha: true, alphaMsg: 'Name can only contain letters and spaces' })) valid = false;
    if (!validateTestimonialField('tmEmail', { required: false, email: true, emailMsg: 'Please enter a valid email' })) valid = false;
    if (!validateTestimonialField('tmFeedback', { required: true, requiredMsg: 'Please write your testimonial', minLength: 10, minLengthMsg: 'Testimonial must be at least 10 characters' })) valid = false;
    
    if (!valid) {
        var firstErr = document.querySelector('.tf-group.tf-error');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
    
    var btn = document.getElementById('tmSubmitBtn');
    btn.classList.add('tf-loading');
    
    var formData = new URLSearchParams();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('city', city);
    formData.append('feedback', feedback);
    
    fetch('../testimonial_process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(function(r) {
        if (!r.ok) {
            return r.json().then(function(errData) {
                throw { status: r.status, body: errData };
            }).catch(function(parseErr) {
                if (parseErr && parseErr.status) throw parseErr;
                throw { status: r.status, body: { message: 'Server error (' + r.status + ')' } };
            });
        }
        return r.json();
    })
    .then(function(data) {
        btn.classList.remove('tf-loading');
        if (data.success) {
            document.getElementById('testimonialForm').style.display = 'none';
            document.getElementById('tmSuccess').style.display = 'block';
        } else {
            if (data.errors) {
                if (data.errors.name) document.getElementById('tmName').closest('.tf-group').classList.add('tf-error');
                if (data.errors.email) document.getElementById('tmEmail').closest('.tf-group').classList.add('tf-error');
                if (data.errors.feedback) document.getElementById('tmFeedback').closest('.tf-group').classList.add('tf-error');
            }
            showFormAlert(data.message || 'Something went wrong. Please try again.', 'error');
        }
    })
    .catch(function(err) {
        btn.classList.remove('tf-loading');
        var msg = 'Network error. Please check your connection and try again.';
        if (err && err.body && err.body.message) msg = err.body.message;
        showFormAlert(msg, 'error');
    });
    
    return false;
}

// ── Testimonial Character Counter ──
(function() {
    var fbEl = document.getElementById('tmFeedback');
    if (fbEl) {
        fbEl.addEventListener('input', function() {
            var len = this.value.length;
            var max = this.maxLength || 500;
            document.getElementById('tmCounter').textContent = len + ' / ' + max;
        });
    }
})();

// ═══════════════════════════════════════════════
// INITIALIZE COMMON UTILITIES
// ═══════════════════════════════════════════════
(function() {
    if (typeof initRippleEffect === 'function') initRippleEffect();
    if (typeof initClearErrorsOnFocus === 'function') initClearErrorsOnFocus();
    if (typeof initSmoothAnchors === 'function') initSmoothAnchors();
    if (typeof initScrollReveal === 'function') {
        initScrollReveal('.reveal-contact');
        initScrollReveal('.reveal-info');
        initScrollReveal('.reveal-form');
    }
})();
