<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Marina Croatia')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Base Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body {
            background: #f9fafc;
            color: #333;
            line-height: 1.5;
        }

        /* Header */
        .header {
          position: absolute; /* make it float over content */
          top: 0;
          left: 0;
          width: 100%;
          padding: 1rem 2rem;
          display: flex;
          justify-content: space-between; /* Home left, Login right */
          align-items: center;
          z-index: 100;
          background: transparent; /* or rgba(0,0,0,0.4) for slight overlay */
        }

        .logo {
            font-size: 1.5rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
        }

         .logo:hover {
            background-color: #2c5aa09b;
            border: 1px solid #709fe49b;
        }


        .header-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .login-btn {
            position: static;
            right: 2rem;
            top: 50%;
            transform: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background 0.2s;
        }

        .login-btn:hover {
            background-color: #2c5aa09b;
            border: 1px solid #709fe49b;
            color: white;
        }

        /* Navigation Bar */
        .navbar {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s;
            font-size: 0.875rem;
        }

        .nav-link:hover {
            color: #3b82f6;
            background: #eff6ff;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-name {
            color: #374151;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        /* Admin/Owner Header */
        .admin-header {
            background: #1e293b;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
        }



        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .nav-item:hover {
            color: #3b82f6;
            background: #eff6ff;
        }

        .nav-item.active {
            color: #3b82f6;
            background: #dbeafe;
            font-weight: 600;
        }

        .nav-user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-role {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .user-role.admin {
            background: #fecaca;
            color: #dc2626;
        }

        .user-role.owner {
            background: #dbeafe;
            color: #3b82f6;
        }

        .user-name {
            font-weight: 600;
            color: #374151;
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .main-title {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: relative;
        }

        .main-title h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .main-title p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }

        .edit-btn {
            top: 12px;
            right: 12px;
            border: none;
            background: #facc15;
            color: #111;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            background: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .card-body {
            padding: 1rem;
        }

        /* Stats cards */
        .stats-card {
            text-align: center;
            padding: 1.5rem;
        }

        .stats-card h3 {
            font-size: 2rem;
            color: #3b82f6;
            margin-bottom: 0.5rem;
        }

        .stats-card p {
            color: #6b7280;
            font-weight: 500;
        }

        /* Status badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-badge.active {
            background: #dcfce7;
            color: #166534;
        }

        .status-badge.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Owner/Location cards */
        .owner-card, .location-card {
            transition: transform 0.2s;
        }

        .owner-card:hover, .location-card:hover {
            transform: translateY(-2px);
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-info {
            background: #0ea5e9;
            color: white;
        }

        .btn-info:hover {
            background: #0284c7;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-content.large {
            max-width: 800px;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .modal-close {
            border: none;
            background: transparent;
            font-size: 22px;
            cursor: pointer;
            padding: 4px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 16px;
        }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        /* Grid */
        .grid {
            display: grid;
            gap: 1rem;
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }

        .grid-4 {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        /* Loading */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .navbar-container {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .nav-links {
                gap: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            }

            .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }
            
            .main-title h1 {
                font-size: 2rem;
            }
            
            .grid-2,
            .grid-3,
            .grid-4 {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Main Header -->
    <header class="header">
        <a href="{{ url('/') }}" class="logo">🏠</a>
        
        @guest
            <a href="{{ route('login') }}" class="login-btn">
                👤 Login
            </a>
        @endguest
    </header>

    <!-- Navigation Bar for Logged-in Users -->
    @auth
    <nav class="navbar">
        <div class="navbar-container">
            <div class="nav-links">
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.locations') }}" class="nav-link">🏖️ Locations</a>
                    <a href="{{ route('admin.houses') }}" class="nav-link">🏘️ Houses</a>
                    <a href="{{ route('admin.owners') }}" class="nav-link">👥 Owners</a>
                    <a href="{{ route('admin.bookings') }}" class="nav-link">📅 All Bookings</a>
                    <a href="{{ route('admin.calendar') }}" class="nav-link">📆 Calendar</a>
                @elseif(auth()->user()->isOwner())
                    <a href="{{ route('admin.locations') }}" class="nav-link">🏖️ Locations</a>
                    <a href="{{ route('owner.bookings') }}" class="nav-link">📅 My Bookings</a>
                    <a href="{{ route('owner.profile') }}" class="nav-link">👤 Profile</a>
                @endif
            </div>
            <div class="user-info">
                <span class="user-name">{{ auth()->user()->full_name }}</span>
                <form action="{{ route('logout') }}" method="POST" style="display: inline; margin-left: 1rem;">
                    @csrf
                    <button type="submit" class="logout-btn">🚪 Logout</button>
                </form>
            </div>
        </div>
    </nav>
    @endauth

    @yield('content')

    <script>
        // CSRF Token for AJAX requests
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };

        // Set CSRF token for all AJAX requests
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof XMLHttpRequest !== 'undefined') {
                XMLHttpRequest.prototype._originalOpen = XMLHttpRequest.prototype.open;
                XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
                    this._originalOpen.apply(this, arguments);
                    this.setRequestHeader('X-CSRF-TOKEN', window.Laravel.csrfToken);
                };
            }

            // Setup fetch to include CSRF token
            const originalFetch = window.fetch;
            window.fetch = function(url, options = {}) {
                options.headers = options.headers || {};
                options.headers['X-CSRF-TOKEN'] = window.Laravel.csrfToken;
                return originalFetch(url, options);
            };
        });

        // User dropdown toggle
        function toggleUserDropdown() {
            const dropdown = document.getElementById('user-dropdown');
            dropdown.classList.toggle('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-menu')) {
                const dropdown = document.getElementById('user-dropdown');
                if (dropdown) {
                    dropdown.classList.remove('active');
                }
            }
        });

        // Modal functionality is handled above

        // Modal management
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = ''; // Restore scrolling
                
                // Reset any forms in the modal
                const forms = modal.querySelectorAll('form');
                forms.forEach(form => form.reset());
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal') && e.target.classList.contains('active')) {
                const modalId = e.target.id;
                closeModal(modalId);
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const activeModal = document.querySelector('.modal.active');
                if (activeModal) {
                    closeModal(activeModal.id);
                }
            }
        });

        // Alert management
        function showAlert(message, type = 'success', duration = 5000) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            
            const container = document.querySelector('.container') || document.body;
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                alertDiv.remove();
            }, duration);
        }
        
        // Form submission with loading
        function submitForm(form, callback) {
            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span> Loading...';
            
            const formData = new FormData(form);
            const method = form.method || 'POST';
            const url = form.action;
            
            fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': window.Laravel.csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    if (callback) callback(data);
                } else {
                    showAlert(data.message || 'An error occurred', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        }
    </script>
    @stack('scripts')
</body>
</html>