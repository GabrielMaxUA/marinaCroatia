@extends('layouts.app')

@section('title', 'My Profile - Marina Croatia')

@section('content')
<div class="container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1>My Profile</h1>
            <p style="color: #6b7280;">Manage your account settings and preferences</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-secondary">← Back to Home</a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Profile Information -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3>Profile Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('owner.profile.update') }}">
                @csrf
                @method('PUT')
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" value="{{ $user->first_name }}" required>
                        @error('first_name')
                        <small style="color: #ef4444;">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" value="{{ $user->last_name }}" required>
                        @error('last_name')
                        <small style="color: #ef4444;">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" value="{{ $user->email }}" disabled style="background: #f3f4f6; color: #6b7280;">
                    <small style="color: #6b7280;">Email address cannot be changed. Contact administrator if needed.</small>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number:</label>
                    <input type="tel" id="phone" name="phone" value="{{ $user->phone }}">
                    @error('phone')
                    <small style="color: #ef4444;">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="preferred_contact_time">Preferred Contact Time:</label>
                    <input type="text" id="preferred_contact_time" name="preferred_contact_time" 
                           value="{{ $user->preferred_contact_time }}" 
                           placeholder="e.g., 9 AM - 5 PM weekdays">
                    @error('preferred_contact_time')
                    <small style="color: #ef4444;">{{ $message }}</small>
                    @enderror
                </div>

                <div style="margin: 2rem 0;">
                    <h4 style="margin-bottom: 1rem;">Notification Preferences</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="notification_email" value="1" 
                                   {{ $user->notification_email ? 'checked' : '' }}>
                            Email Notifications
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="notification_sms" value="1" 
                                   {{ $user->notification_sms ? 'checked' : '' }}>
                            SMS Notifications
                        </label>
                    </div>
                    <small style="color: #6b7280;">
                        Receive notifications about booking requests, confirmations, and important updates.
                    </small>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Account Information -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3>Account Information</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <h4 style="margin-bottom: 1rem;">Account Details</h4>
                    <p><strong>User ID:</strong> {{ $user->id }}</p>
                    <p><strong>Role:</strong> Property Owner</p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge {{ $user->is_active ? 'active' : 'inactive' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                    <p><strong>Member Since:</strong> {{ $user->created_at->format('F j, Y') }}</p>
                    <p><strong>Last Updated:</strong> {{ $user->updated_at->format('F j, Y \a\t g:i A') }}</p>
                </div>
                <div>
                    <h4 style="margin-bottom: 1rem;">Property Statistics</h4>
                    @php
                        $houses = $user->houses;
                        $totalSuites = $houses->sum(fn($house) => $house->suites->count());
                        $totalBookings = 0;
                        foreach($houses as $house) {
                            foreach($house->suites as $suite) {
                                $totalBookings += $suite->bookings()->where('is_owner_booking', true)->count();
                            }
                        }
                    @endphp
                    <p><strong>Properties:</strong> {{ $houses->count() }}</p>
                    <p><strong>Total Suites:</strong> {{ $totalSuites }}</p>
                    <p><strong>Total Bookings:</strong> {{ $totalBookings }}</p>
                    
                    @if($houses->count() > 0)
                    <div style="margin-top: 1rem;">
                        <strong>Properties:</strong>
                        @foreach($houses as $house)
                        <div style="margin-top: 0.5rem; padding: 0.5rem; background: #f8f9fa; border-radius: 4px;">
                            <strong>{{ $house->name }}</strong><br>
                            <small style="color: #6b7280;">{{ $house->location->name }} • {{ $house->suites->count() }} suites</small>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="card">
        <div class="card-header">
            <h3>Change Password</h3>
        </div>
        <div class="card-body">
            <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 1rem; margin-bottom: 2rem;">
                <p style="margin: 0; color: #92400e;">
                    <strong>Security Notice:</strong> For security reasons, password changes must be handled by the administrator. 
                    Please contact the system administrator if you need to change your password.
                </p>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="contactAdmin()">Contact Administrator</button>
                <button type="button" class="btn btn-info" onclick="showPasswordPolicy()">View Password Policy</button>
            </div>
        </div>
    </div>
</div>

<!-- Password Policy Modal -->
<div id="password-policy-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Password Policy</h3>
            <button class="modal-close" onclick="closeModal('password-policy-modal')">×</button>
        </div>
        <div class="modal-body">
            <h4>Password Requirements:</h4>
            <ul style="margin: 1rem 0; padding-left: 1.5rem;">
                <li>Minimum 8 characters long</li>
                <li>Contains at least one uppercase letter</li>
                <li>Contains at least one lowercase letter</li>
                <li>Contains at least one number</li>
                <li>Contains at least one special character</li>
            </ul>
            
            <h4>Security Best Practices:</h4>
            <ul style="margin: 1rem 0; padding-left: 1.5rem;">
                <li>Use a unique password not used elsewhere</li>
                <li>Don't share your password with others</li>
                <li>Change your password regularly</li>
                <li>Use a password manager if possible</li>
            </ul>
            
            <div style="margin-top: 2rem; text-align: center;">
                <button class="btn btn-primary" onclick="closeModal('password-policy-modal')">Got it</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        document.forms[0].reset();
        location.reload();
    }
}

function contactAdmin() {
    const subject = encodeURIComponent('Password Change Request - Marina Croatia');
    const body = encodeURIComponent(`Hello Administrator,

I would like to request a password change for my Marina Croatia owner account.

Account Details:
- Name: {{ $user->full_name }}
- Email: {{ $user->email }}
- User ID: {{ $user->id }}

Please let me know the next steps.

Best regards,
{{ $user->full_name }}`);

    // Try to open default email client
    window.location.href = `mailto:admin@marinacroatia.com?subject=${subject}&body=${body}`;
}

function showPasswordPolicy() {
    openModal('password-policy-modal');
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    
    if (!firstName || !lastName) {
        e.preventDefault();
        showAlert('First name and last name are required', 'error');
        return false;
    }
    
    // Phone validation (if provided)
    const phone = document.getElementById('phone').value.trim();
    if (phone && !phone.match(/^[\d\s\+\-\(\)]+$/)) {
        e.preventDefault();
        showAlert('Please enter a valid phone number', 'error');
        return false;
    }
});

// Auto-save draft changes (optional enhancement)
let saveTimeout;
const formInputs = document.querySelectorAll('input[type="text"], input[type="tel"], input[type="checkbox"]');

formInputs.forEach(input => {
    input.addEventListener('change', function() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            // Could implement auto-save draft functionality here
            console.log('Form data changed - could auto-save draft');
        }, 2000);
    });
});
</script>
@endpush
@endsection