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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem 2rem;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            position: relative;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c5aa0;
            text-align: center;
        }

        .header-image {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .user-menu {
            position: absolute;
            right: 2rem;
            top: 50%;
            transform: translateY(-50%);
            display: inline-block;
        }

        .user-icon {
            width: 40px;
            height: 40px;
            background: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s;
        }

        .user-icon:hover {
            background: #e0e0e0;
        }

        .login-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background 0.2s;
        }

        .login-btn:hover {
            background: #2563eb;
            color: white;
        }

        .user-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            min-width: 200px;
            z-index: 1000;
            margin-top: 0.5rem;
        }

        .user-dropdown.active {
            display: block;
        }

        .dropdown-item {
            display: block;
            padding: 12px 16px;
            text-decoration: none;
            color: #333;
            border-bottom: 1px solid #f0f0f0;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
        }

        .dropdown-item:last-child {
            border-bottom: none;
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

        .admin-logo {
            font-size: 18px;
            font-weight: bold;
        }

        .admin-badge {
            background: #22c55e;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
        }

        .owner-badge {
            background: #3b82f6;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
        }

        .admin-controls {
            display: flex;
            gap: 10px;
        }

        .admin-btn {
            background: #334155;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .admin-btn:hover {
            background: #475569;
        }

        .admin-btn.success {
            background: #22c55e;
        }

        .admin-btn.success:hover {
            background: #16a34a;
        }

        .admin-btn.danger {
            background: #ef4444;
        }

        .admin-btn.danger:hover {
            background: #dc2626;
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
            position: absolute;
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
        <a href="{{ url('/') }}" class="logo">MarinaCroatia.com</a>
        
        <div class="user-menu">
            @auth
                <div class="user-icon" onclick="toggleUserDropdown()">
                    @if(auth()->user()->isAdmin())
                        <span>A</span>
                    @elseif(auth()->user()->isOwner())
                        <span>O</span>
                    @else
                        <span>U</span>
                    @endif
                </div>
                <div id="user-dropdown" class="user-dropdown">
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="dropdown-item">🏠 Dashboard</a>
                        <a href="{{ route('admin.owners') }}" class="dropdown-item">👥 Owners</a>
                        <a href="{{ route('admin.bookings') }}" class="dropdown-item">📅 Bookings</a>
                        <div style="border-top: 1px solid #f0f0f0; margin: 0.5rem 0;"></div>
                    @elseif(auth()->user()->isOwner())
                        <a href="{{ route('owner.dashboard') }}" class="dropdown-item">🏠 Dashboard</a>
                        <a href="{{ route('owner.bookings') }}" class="dropdown-item">📅 My Bookings</a>
                        <a href="{{ route('owner.profile') }}" class="dropdown-item">👤 Profile</a>
                        <div style="border-top: 1px solid #f0f0f0; margin: 0.5rem 0;"></div>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; border: none; background: none; cursor: pointer;">🚪 Logout</button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="login-btn">
                    👤 Login
                </a>
            @endauth
        </div>
    </header>

    @if(auth()->check())
        @if(auth()->user()->isAdmin())
            <div class="admin-header">
                <div class="admin-logo">ADMIN PANEL</div>
                <div class="admin-badge">ADMIN MODE</div>
                <div class="admin-controls">
                    <a href="{{ route('admin.dashboard') }}" class="admin-btn">Dashboard</a>
                    <a href="{{ route('admin.houses') }}" class="admin-btn">Houses</a>
                    <a href="{{ route('admin.owners') }}" class="admin-btn">Owners</a>
                    <a href="{{ route('admin.bookings') }}" class="admin-btn">Bookings</a>
                </div>
            </div>
        @elseif(auth()->user()->isOwner())
            <div class="admin-header">
                <div class="admin-logo">OWNER PANEL</div>
                <div class="owner-badge">{{ auth()->user()->full_name }}</div>
                <div class="admin-controls">
                    <a href="{{ route('owner.dashboard') }}" class="admin-btn">Dashboard</a>
                    <a href="{{ route('owner.bookings') }}" class="admin-btn">Bookings</a>
                    <a href="{{ route('owner.profile') }}" class="admin-btn">Profile</a>
                </div>
            </div>
        @endif
    @endif

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