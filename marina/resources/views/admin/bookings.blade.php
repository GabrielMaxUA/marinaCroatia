@extends('layouts.app')

@section('title', 'Bookings Management - Marina Croatia')

@section('content')
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Bookings Management</h2>
        <div style="display: flex; gap: 1rem;">
            <button class="btn btn-success" onclick="openCalendarView()">📅 Calendar View</button>
            <button class="btn btn-primary" onclick="openModal('booking-modal')">+ New Booking</button>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3>Filters</h3>
        </div>
        <div class="card-body">
            <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label for="source_filter">Booking Source:</label>
                    <select id="source_filter" name="source">
                        <option value="">All Sources</option>
                        <option value="admin" {{ request('source') == 'admin' ? 'selected' : '' }}>Admin Bookings</option>
                        <option value="owner" {{ request('source') == 'owner' ? 'selected' : '' }}>Owner Bookings</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label for="location_filter">Location:</label>
                    <select id="location_filter" name="location_id">
                        <option value="">All Locations</option>
                        @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label for="owner_filter">Owner:</label>
                    <select id="owner_filter" name="owner_id">
                        <option value="">All Owners</option>
                        @foreach($owners as $owner)
                        <option value="{{ $owner->id }}" {{ request('owner_id') == $owner->id ? 'selected' : '' }}>
                            {{ $owner->full_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label for="house_filter">House:</label>
                    <select id="house_filter" name="house_id">
                        <option value="">All Houses</option>
                        @foreach($houses as $house)
                        <option value="{{ $house->id }}" {{ request('house_id') == $house->id ? 'selected' : '' }}>
                            {{ $house->name }} ({{ $house->location->name }})
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label for="suite_filter">Suite:</label>
                    <select id="suite_filter" name="suite_id">
                        <option value="">All Suites</option>
                        @foreach($suites as $suite)
                        <option value="{{ $suite->id }}" {{ request('suite_id') == $suite->id ? 'selected' : '' }}>
                            {{ $suite->name }} ({{ $suite->house->name }})
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label for="status_filter">Status:</label>
                    <select id="status_filter" name="status">
                        <option value="">All Statuses</option>
                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.bookings') }}" class="btn btn-secondary">Clear</a>
                </div>
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
                                <th>Source</th>
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
                                    <span class="badge" style="background: {{ $booking->is_owner_booking ? '#3b82f6' : '#10b981' }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        {{ $booking->is_owner_booking ? 'Owner' : 'Admin' }}
                                    </span>
                                    @if($booking->is_owner_booking && $booking->suite->house->owner)
                                    <br><small style="color: #6b7280;">{{ $booking->suite->house->owner->full_name }}</small>
                                    @endif
                                </td>
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
                                        @if(!$booking->is_owner_booking)
                                        <button class="btn btn-secondary" style="font-size: 12px; padding: 4px 8px;" onclick="editBooking({{ $booking->id }})">
                                            Edit
                                        </button>
                                        @endif
                                        @if(!$booking->cancelled_at && (!$booking->is_owner_booking || auth()->user()->isAdmin()))
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
            {{ $bookings->appends(request()->query())->links() }}
        </div>
        @endif

    @else
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 3rem;">
                <h3 style="color: #6b7280; margin-bottom: 1rem;">No Bookings Found</h3>
                <p style="color: #9ca3af;">
                    @if(request()->hasAny(['source', 'location_id', 'owner_id', 'house_id', 'suite_id', 'status']))
                        No bookings match your current filters. Try adjusting your search criteria.
                    @else
                        No bookings have been created yet. Create your first booking to get started!
                    @endif
                </p>
                <div style="margin-top: 2rem;">
                    <button class="btn btn-primary" onclick="openModal('booking-modal')">Create First Booking</button>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Calendar View Modal -->
<div id="calendar-view-modal" class="modal">
    <div class="modal-content" style="max-width: 95vw; width: 1200px;">
        <div class="modal-header">
            <h3>Bookings Calendar</h3>
            <button class="modal-close" onclick="closeModal('calendar-view-modal')">×</button>
        </div>
        <div class="modal-body">
            <div style="display: flex; align-items: center; justify-content: center; gap: 2rem; margin-bottom: 1rem;">
                <button class="btn btn-secondary" onclick="prevCalendarMonth()">‹ Previous</button>
                <h3 id="calendar-view-month">Loading...</h3>
                <button class="btn btn-secondary" onclick="nextCalendarMonth()">Next ›</button>
            </div>
            
            <div id="calendar-view-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; margin-bottom: 1rem;">
                <!-- Calendar will be populated here -->
            </div>
            
            <div style="display: flex; justify-content: space-around; font-size: 14px; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 15px; height: 15px; background: #e8f5e8; border: 1px solid #4caf50; border-radius: 3px;"></div>
                    <span>Available</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 15px; height: 15px; background: #e3f2fd; border: 1px solid #2196f3; border-radius: 3px;"></div>
                    <span>Owner Booking</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 15px; height: 15px; background: #e8f5e8; border: 1px solid #4caf50; border-radius: 3px;"></div>
                    <span>Admin Booking</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 15px; height: 15px; background: #ffebee; border: 1px solid #f44336; border-radius: 3px;"></div>
                    <span>Multiple Bookings</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Booking Modal -->
<div id="booking-modal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3 id="booking-modal-title">New Admin Booking</h3>
            <button class="modal-close" onclick="closeModal('booking-modal')">×</button>
        </div>
        <form id="booking-form" onsubmit="saveBooking(event)">
            <div class="form-group">
                <label for="booking-suite">Suite:</label>
                <select id="booking-suite" name="suite_id" required>
                    <option value="">Select Suite</option>
                    @foreach($suites as $suite)
                    <option value="{{ $suite->id }}">
                        {{ $suite->house->location->name }} - {{ $suite->house->name }} - {{ $suite->name }} ({{ $suite->capacity_people }} people)
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="booking-guest-name">Guest Name:</label>
                    <input type="text" id="booking-guest-name" name="guest_name" required>
                </div>
                <div class="form-group">
                    <label for="booking-guest-phone">Guest Phone:</label>
                    <input type="tel" id="booking-guest-phone" name="guest_phone" required>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="booking-check-in">Check-in Date:</label>
                    <input type="date" id="booking-check-in" name="check_in" required>
                </div>
                <div class="form-group">
                    <label for="booking-check-out">Check-out Date:</label>
                    <input type="date" id="booking-check-out" name="check_out" required>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="booking-guest-quantity">Number of Guests:</label>
                    <input type="number" id="booking-guest-quantity" name="guest_quantity" min="1" required>
                </div>
                <div class="form-group">
                    <label for="booking-deposit-amount">Deposit Amount (€):</label>
                    <input type="number" id="booking-deposit-amount" name="deposit_amount" step="0.01" min="0">
                </div>
                <div class="form-group" style="display: flex; align-items: end; gap: 1rem; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="deposit_paid">
                        Deposit Paid
                    </label>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="parking_needed">
                    Parking Needed
                </label>
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="pets_allowed">
                    Pets Allowed
                </label>
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="has_small_kids">
                    Small Kids
                </label>
            </div>
            
            <div class="form-group">
                <label for="booking-notes">Notes (optional):</label>
                <textarea id="booking-notes" name="notes" rows="3" placeholder="Special requests, additional information..."></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('booking-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Booking</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let currentCalendarMonth = new Date().getMonth();
let currentCalendarYear = new Date().getFullYear();
let editingBooking = null;

function openCalendarView() {
    openModal('calendar-view-modal');
    loadCalendarView();
}

function loadCalendarView() {
    // This would load all bookings for the calendar view
    // For now, we'll show a simple message
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                      'July', 'August', 'September', 'October', 'November', 'December'];
    
    document.getElementById('calendar-view-month').textContent = `${monthNames[currentCalendarMonth]} ${currentCalendarYear}`;
    
    const grid = document.getElementById('calendar-view-grid');
    grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #6b7280;">Calendar view coming soon! This will show all bookings across all properties.</div>';
}

function prevCalendarMonth() {
    if (currentCalendarMonth === 0) {
        currentCalendarMonth = 11;
        currentCalendarYear--;
    } else {
        currentCalendarMonth--;
    }
    loadCalendarView();
}

function nextCalendarMonth() {
    if (currentCalendarMonth === 11) {
        currentCalendarMonth = 0;
        currentCalendarYear++;
    } else {
        currentCalendarMonth++;
    }
    loadCalendarView();
}

function saveBooking(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    const url = editingBooking 
        ? `{{ url('admin/bookings') }}/${editingBooking}`
        : '{{ route("admin.bookings.create") }}';
    
    if (editingBooking) {
        formData.append('_method', 'PUT');
    }
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': window.Laravel.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('booking-modal');
            showAlert(data.message, 'success');
            form.reset();
            editingBooking = null;
            document.getElementById('booking-modal-title').textContent = 'New Admin Booking';
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

function viewBooking(bookingId) {
    showAlert('Booking details view coming soon!', 'info');
}

function editBooking(bookingId) {
    showAlert('Booking editing coming soon!', 'info');
}

function cancelBooking(bookingId) {
    if (!confirm('Are you sure you want to cancel this booking?')) {
        return;
    }
    
    fetch(`{{ url('admin/bookings') }}/${bookingId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': window.Laravel.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
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

// Set minimum date for check-in to today
document.getElementById('booking-check-in').min = new Date().toISOString().split('T')[0];

// Update check-out minimum date when check-in changes
document.getElementById('booking-check-in').addEventListener('change', function() {
    const checkIn = new Date(this.value);
    checkIn.setDate(checkIn.getDate() + 1);
    document.getElementById('booking-check-out').min = checkIn.toISOString().split('T')[0];
});

// Reset form when opening booking modal
document.getElementById('booking-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        editingBooking = null;
        document.getElementById('booking-modal-title').textContent = 'New Admin Booking';
        document.getElementById('booking-form').reset();
    }
});
</script>
@endpush
@endsection