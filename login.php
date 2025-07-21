<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQMS - Staff Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            min-height: 100vh;
            align-items: center;
        }
        .auth-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .auth-header {
            display: flex;
            border-bottom: 1px solid #eee;
        }
        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .auth-tab.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
        }
        .auth-tab:not(.active):hover {
            background-color: #f8f9fa;
        }
        .auth-body {
            padding: 2rem;
        }
        .auth-form {
            display: none;
        }
        .auth-form.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
        }
        .btn-auth {
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 500;
        }
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #6c757d;
        }
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }
        .divider::before {
            margin-right: 1rem;
        }
        .divider::after {
            margin-left: 1rem;
        }
        .alert-position {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            width: 350px;
        }
    </style>
</head>
<body>
    <!-- Alert Container -->
    <div class="alert-position" id="alertContainer"></div>

    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-tab active" id="loginTab" onclick="switchTab('login')">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </div>
            <div class="auth-tab" id="registerTab" onclick="switchTab('register')">
                <i class="bi bi-person-plus me-2"></i>Register
            </div>
        </div>
        
        <div class="auth-body">
            <!-- Login Form -->
            <form id="loginForm" class="auth-form active">
                <div class="mb-3">
                    <label for="loginEmail" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="loginEmail" required>
                </div>
                <div class="mb-3">
                    <label for="loginPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="loginPassword" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                    <a href="#" class="float-end text-decoration-none">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary btn-auth w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
                <div class="divider">or</div>
                <button type="button" class="btn btn-outline-secondary btn-auth w-100" onclick="switchTab('register')">
                    Create new account
                </button>
            </form>
            
            <!-- Registration Form -->
            <form id="registerForm" class="auth-form">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="regFirstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="regFirstName" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="regLastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="regLastName" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="regEmail" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="regEmail" required>
                </div>
                <div class="mb-3">
                    <label for="regPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="regPassword" required minlength="8">
                </div>
                <div class="mb-3">
                    <label for="regConfirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="regConfirmPassword" required>
                </div>
                <div class="mb-3">
                    <label for="regRole" class="form-label">Role</label>
                    <select class="form-select" id="regRole" required>
                        <option value="" disabled selected>Select your role</option>
                        <option value="doctor">Doctor</option>
                        <option value="nurse">Nurse</option>
                        <option value="receptionist">Receptionist</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                    <label class="form-check-label" for="agreeTerms">I agree to the <a href="#">terms and conditions</a></label>
                </div>
                <button type="submit" class="btn btn-primary btn-auth w-100 mb-3">
                    <i class="bi bi-person-plus me-2"></i>Register
                </button>
                <div class="divider">or</div>
                <button type="button" class="btn btn-outline-secondary btn-auth w-100" onclick="switchTab('login')">
                    Already have an account? Login
                </button>
            </form>
        </div>
    </div>

    <script>
        // Alert function
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            const alertId = 'alert-' + Date.now();
            
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.role = 'alert';
            alert.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2"></i>
                    <div>${message}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            alertContainer.appendChild(alert);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        }

        function switchTab(tabName) {
            // Update tabs
            document.getElementById('loginTab').classList.toggle('active', tabName === 'login');
            document.getElementById('registerTab').classList.toggle('active', tabName === 'register');
            
            // Update forms
            document.getElementById('loginForm').classList.toggle('active', tabName === 'login');
            document.getElementById('registerForm').classList.toggle('active', tabName === 'register');
        }

        // Form submissions
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
        
            fetch('php/api_register_staff.php', {
            method: 'POST',
             headers: {
                       'Content-Type': 'application/x-www-form-urlencoded'
                 },
               body: new URLSearchParams({
                              email: document.getElementById('loginEmail').value,
                           password: document.getElementById('loginPassword').value
             })
          })
             .then(res => res.json())
             .then(response => {
        if (response.token) {
                     // Login successful, you can store the token if needed
                    // console.log('Login successful! Token: ' + response.token);
                     localStorage.setItem("pqms", response.token);
                     localStorage.setItem("staff", JSON.stringify(response.data));
                     showAlert('success', 'Login successful! Redirecting to dashboard...');
                    // Optionally, redirect or save token to localStorage/sessionStorage
                    setTimeout(() => { window.location.href = "dashboard.php" }, 1000);
                   } else {
                              showAlert('Login failed: ' + (data.message || 'Unknown error'));
                               //showAlert('danger', 'Invalid email or password. Please try again.');
                      }
              });
        });

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('regPassword').value;
            const confirmPassword = document.getElementById('regConfirmPassword').value;
            
            // Validate password match
            if (password !== confirmPassword) {
                showAlert('danger', 'Passwords do not match!');
                return;
            }
            
            // Simulate registration request
            fetch('php/api_register_staff.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                firstName: document.getElementById('regFirstName').value,
                lastName: document.getElementById('regLastName').value,
                email: document.getElementById('regEmail').value,
                password: password,
                role: document.getElementById('regRole').value
              })
            })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                showAlert('success', 'Registration successful! You can now login.');
                switchTab('login');
                document.getElementById('registerForm').reset();
              } else {
                showAlert('danger', 'Registration failed: ' + (data.error || 'Unknown error'));
              }
            })
            .catch(err => {
              showAlert('danger', 'Registration failed: ' + err.message);
            });
        });

        // Initialize Bootstrap tooltips (for any elements that might need them)
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>