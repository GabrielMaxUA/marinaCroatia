@extends('layouts.app')

@section('title', 'My Bookings - Marina Croatia')

@section('content')
<div class="container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1>My Bookings</h1>
            <p style="color: #6b7280;">Manage your property bookings</p>
        </div>
        <a href="{{ route('owner.dashboard') }}" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <form method="GET" style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="house_id">Filter by Property:</label>
                    <select name="house_id" id="house_id" onchange="this.form.submit()">
                        <option value="">All Properties</option>
                        @foreach(auth()->user()->houses as $house)
                        <option value="{{ $house->id }}" {{ request('house_id') == $house->id ? 'selected' : '' }}>
                            {{ $house->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="suite_id">Filter by Suite:</label>
                    <select name="suite_id" id="suite_id" onchange="this.form.submit()">
                        <option value="">All Suites</option>
                        @if(request('house_id'))
                            @php
                                $house = auth()->user()->houses->find(request('house_id'));
                            @endphp
                            @if($house)
                                @foreach($house->suites as $suite)
                                <option value="{{ $suite->id }}" {{ request('suite_id') == $suite->id ? 'selected' : '' }}>
                                    {{ $suite->name }}
                                </option>
                                @endforeach
                            @endif
                        @endif
                    </select>
                </div>
                @if(request()->hasAny(['house_id', 'suite_id']))
                <a href="{{ route('owner.bookings') }}" class="btn btn-secondary">Clear Filters</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Bookings List -->
    @if($bookings->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3>Bookings ({{ $bookings->total() }})</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Guest</th>
                                <th>Property & Suite</th>
                                <th>Dates</th>
                                <th>Guests</th>
                                <th>Status</th>
                                <th>Deposit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $booking->guest_name }}</strong><br>
                                        <small style="color: #6b7280;">{{ $booking->guest_phone }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $booking->suite->house->name }}</strong><br>
                                        <small style="color: #6b7280;">{{ $booking->suite->name }} • {{ $booking->suite->house->location->name }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $booking->check_in->format('M d, Y') }}</strong><br>
                                        <small style="color: #6b7280;">to {{ $booking->check_out->format('M d, Y') }}</small><br>
                                        <small style="color: #9ca3af;">{{ $booking->total_nights }} nights</small>
                                    </div>
                                </td>
                                <td>{{ $booking->guest_quantity }}</td>
                                <td>
                                    @if($booking->cancelled_at)
                                        <span class="badge" style="background: #ef4444; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            Cancelled
                                        </span>
                                    @elseif($booking->check_out < now())
                                        <span class="badge" style="background: #6b7280; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            Completed
                                        </span>
                                    @elseif($booking->check_in <= now() && $booking->check_out >= now())
                                        <span class="badge" style="background: #10b981; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge" style="background: #3b82f6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            Upcoming
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->deposit_paid)
                                        <span style="color: #10b981;">✓ Paid</span>
                                        @if($booking->deposit_amount)
                                        <br><small>€{{ number_format($booking->deposit_amount, 2) }}</small>
                                        @endif
                                    @else
                                        <span style="color: #ef4444;">✗ Unpaid</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.25rem; flex-direction: column;">
                                        <button class="btn btn-primary" style="font-size: 12px; padding: 4px 8px;" onclick="viewBooking({{ $booking->id }})">
                                            View
                                        </button>
                                        @if(!$booking->cancelled_at && $booking->check_in > now())
                                        <button class="btn btn-secondary" style="font-size: 12px; padding: 4px 8px;" onclick="editBooking({{ $booking->id }})">
                                            Edit
                                        </button>
                                        <button class="btn btn-danger" style="font-size: 12px; padding: 4px 8px;" onclick="cancelBooking({{ $booking->id }})">
                                            Cancel
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if($bookings->hasPages())
        <div style="margin-top: 2rem;">
            {{ $bookings->links() }}
        </div>
        @endif

    @else
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 3rem;">
                <h3 style="color: #6b7280; margin-bottom: 1rem;">No Bookings Found</h3>
                <p style="color: #9ca3af;">
                    @if(request()->hasAny(['house_id', 'suite_id']))
                        No bookings found with the current filters. <a href="{{ route('owner.bookings') }}">View all bookings</a>
                    @else
                        You don't have any bookings yet. Start managing your calendar to receive bookings!
                    @endif
                </p>
                <div style="margin-top: 2rem;">
                    <a href="{{ route('owner.dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Booking Details Modal -->
<div id="booking-details-modal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Booking Details</h3>
            <button class="modal-close" onclick="closeModal('booking-details-modal')">×</button>
        </div>
        <div id="booking-details-content">
            <!-- Booking details will be populated here -->
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewBooking(bookingId) {
    fetch(`{{ url('owner/bookings') }}/${bookingId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayBookingDetails(data.booking, data.can_edit, data.source_label);
            } else {
                showAlert(data.message || 'Error loading booking details', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error loading booking details', 'error');
        });
}

function displayBookingDetails(booking, canEdit, sourceLabel) {
    const content = document.getElementById('booking-details-content');
    const cancelledInfo = booking.cancelled_at ? `
        <div class="alert alert-error">
            This booking was cancelled on ${new Date(booking.cancelled_at).toLocaleDateString()}
        </div>
    ` : '';
    
    content.innerHTML = `
        ${cancelledInfo}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>
                <h4 style="margin-bottom: 1rem;">Guest Information</h4>
                <p><strong>Name:</strong> ${booking.guest_name}</p>
                <p><strong>Phone:</strong> ${booking.guest_phone}</p>
                <p><strong>Number of Guests:</strong> ${booking.guest_quantity}</p>
                
                <h4 style="margin: 2rem 0 1rem 0;">Booking Details</h4>
                <p><strong>Check-in:</strong> ${new Date(booking.check_in).toLocaleDateString()}</p>
                <p><strong>Check-out:</strong> ${new Date(booking.check_out).toLocaleDateString()}</p>
                <p><strong>Total Nights:</strong> ${booking.total_nights}</p>
                <p><strong>Source:</strong> ${sourceLabel}</p>
            </div>
            
            <div>
                <h4 style="margin-bottom: 1rem;">Property Information</h4>
                <p><strong>Location:</strong> ${booking.suite.house.location.name}</p>
                <p><strong>Property:</strong> ${booking.suite.house.name}</p>
                <p><strong>Suite:</strong> ${booking.suite.name}</p>
                
                <h4 style="margin: 2rem 0 1rem 0;">Additional Services</h4>
                <p><strong>Parking:</strong> ${booking.parking_needed ? 'Yes' : 'No'}</p>
                <p><strong>Pets:</strong> ${booking.pets_allowed ? 'Yes' : 'No'}</p>
                <p><strong>Small Kids:</strong> ${booking.has_small_kids ? 'Yes' : 'No'}</p>
                
                <h4 style="margin: 2rem 0 1rem 0;">Payment</h4>
                <p><strong>Deposit Status:</strong> ${booking.deposit_paid ? 'Paid' : 'Unpaid'}</p>
                ${booking.deposit_amount ? `<p><strong>Deposit Amount:</strong> €${parseFloat(booking.deposit_amount).toFixed(2)}</p>` : ''}
            </div>
        </div>
        
        ${booking.notes ? `
        <div style="margin-top: 2rem;">
            <h4 style="margin-bottom: 1rem;">Notes</h4>
            <p style="background: #f9fafb; padding: 1rem; border-radius: 6px; border-left: 4px solid #3b82f6;">${booking.notes}</p>
        </div>
        ` : ''}
        
        <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
            ${canEdit && !booking.cancelled_at ? `
                <button class="btn btn-secondary" onclick="editBooking(${booking.id})">Edit Booking</button>
                <button class="btn btn-danger" onclick="cancelBooking(${booking.id})">Cancel Booking</button>
            ` : ''}
            <button class="btn btn-primary" onclick="closeModal('booking-details-modal')">Close</button>
        </div>
    `;
    
    openModal('booking-details-modal');
}

function editBooking(bookingId) {
    showAlert('Booking editing feature coming soon!', 'warning');
}

function cancelBooking(bookingId) {
    if (!confirm('Are you sure you want to cancel this booking?')) {
        return;
    }
    
    fetch(`{{ url('owner/bookings') }}/${bookingId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': window.Laravel.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closeModal('booking-details-modal');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showAlert(data.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'error');
    });
}

// Update suite dropdown when house changes
document.getElementById('house_id').addEventListener('change', function() {
    const houseId = this.value;
    const suiteSelect = document.getElementById('suite_id');
    
    // Clear existing options
    suiteSelect.innerHTML = '<option value="">All Suites</option>';
    
    if (houseId) {
        // You would normally make an AJAX call here to get suites for the selected house
        // For now, we'll just submit the form to reload with the house filter
        this.form.submit();
    }
});
</script>
@endpush
@endsection