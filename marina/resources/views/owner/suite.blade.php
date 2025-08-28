@extends('layouts.app')

@section('title', $suite->name . ' - Marina Croatia')

@section('content')
<div class="container">
    <!-- Breadcrumb -->
    <nav style="margin-bottom: 2rem;">
        <a href="{{ route('owner.dashboard') }}" class="btn btn-secondary">← Dashboard</a>
        <a href="{{ route('owner.house', $suite->house) }}" class="btn btn-secondary">{{ $suite->house->name }}</a>
    </nav>

    <!-- Suite Details -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>{{ $suite->name }}</h1>
                <p style="margin: 0; color: #6b7280;">
                    {{ $suite->house->name }}, {{ $suite->house->location->name }}
                </p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <span class="status-badge {{ $suite->is_active ? 'active' : 'inactive' }}">
                    {{ $suite->is_active ? 'Active' : 'Inactive' }}
                </span>
                <button class="btn btn-success" onclick="openCalendar()">Manage Calendar</button>
            </div>
        </div>
        <div class="card-body">
            <div class="grid grid-2" style="gap: 2rem;">
                <div>
                    <h3 style="margin-bottom: 1rem;">Suite Details</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <p><strong>Capacity:</strong> {{ $suite->capacity_people }} people</p>
                        <p><strong>Bedrooms:</strong> {{ $suite->bedrooms }}</p>
                        <p><strong>Bathrooms:</strong> {{ $suite->bathrooms }}</p>
                        @if($suite->floor_number)
                        <p><strong>Floor:</strong> {{ $suite->floor_number }}</p>
                        @endif
                    </div>
                    @if($suite->description)
                    <div>
                        <strong>Description:</strong>
                        <p style="margin-top: 0.5rem;">{{ $suite->description }}</p>
                    </div>
                    @endif
                </div>
                <div>
                    <h3 style="margin-bottom: 1rem;">Booking Statistics</h3>
                    @php
                        $totalBookings = $suite->bookings()->where('is_owner_booking', true)->whereNull('cancelled_at')->count();
                        $activeBookings = $suite->bookings()
                            ->where('is_owner_booking', true)
                            ->whereNull('cancelled_at')
                            ->where('check_out', '>=', now())
                            ->count();
                        $upcomingBookings = $suite->bookings()
                            ->where('is_owner_booking', true)
                            ->whereNull('cancelled_at')
                            ->where('check_in', '>', now())
                            ->count();
                        $completedBookings = $suite->bookings()
                            ->where('is_owner_booking', true)
                            ->whereNull('cancelled_at')
                            ->where('check_out', '<', now())
                            ->count();
                    @endphp
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="card" style="text-align: center; padding: 1rem;">
                            <h3 style="color: #3b82f6; font-size: 2rem; margin: 0;">{{ $totalBookings }}</h3>
                            <p style="margin: 0; font-size: 14px;">Total Bookings</p>
                        </div>
                        <div class="card" style="text-align: center; padding: 1rem;">
                            <h3 style="color: #10b981; font-size: 2rem; margin: 0;">{{ $activeBookings }}</h3>
                            <p style="margin: 0; font-size: 14px;">Active Now</p>
                        </div>
                        <div class="card" style="text-align: center; padding: 1rem;">
                            <h3 style="color: #f59e0b; font-size: 2rem; margin: 0;">{{ $upcomingBookings }}</h3>
                            <p style="margin: 0; font-size: 14px;">Upcoming</p>
                        </div>
                        <div class="card" style="text-align: center; padding: 1rem;">
                            <h3 style="color: #6b7280; font-size: 2rem; margin: 0;">{{ $completedBookings }}</h3>
                            <p style="margin: 0; font-size: 14px;">Completed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button class="btn btn-primary" onclick="openBookingModal()">+ New Booking</button>
                <button class="btn btn-success" onclick="openCalendar()">View Calendar</button>
                <a href="{{ route('owner.bookings', ['suite_id' => $suite->id]) }}" class="btn btn-secondary">
                    All Bookings ({{ $totalBookings }})
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3>Recent Bookings</h3>
            <a href="{{ route('owner.bookings', ['suite_id' => $suite->id]) }}" class="btn btn-primary">View All</a>
        </div>
        <div class="card-body">
            @php
                $recentBookings = $suite->bookings()
                    ->where('is_owner_booking', true)
                    ->whereNull('cancelled_at')
                    ->latest()
                    ->limit(5)
                    ->get();
            @endphp

            @if($recentBookings->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Guest</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Guests</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBookings as $booking)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $booking->guest_name }}</strong>
                                        @if($booking->guest_phone)
                                        <br><small style="color: #6b7280;">{{ $booking->guest_phone }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $booking->check_in->format('M d, Y') }}</td>
                                <td>{{ $booking->check_out->format('M d, Y') }}</td>
                                <td>{{ $booking->guest_quantity }}</td>
                                <td>
                                    @if($booking->check_out < now())
                                        <span class="badge" style="background: #6b7280; color: white;">Completed</span>
                                    @elseif($booking->check_in <= now() && $booking->check_out >= now())
                                        <span class="badge" style="background: #10b981; color: white;">Active</span>
                                    @else
                                        <span class="badge" style="background: #3b82f6; color: white;">Upcoming</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;" 
                                            onclick="viewBookingDetails({{ $booking->id }})">
                                        View
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 3rem;">
                    <h4 style="color: #6b7280; margin-bottom: 1rem;">No Bookings Yet</h4>
                    <p style="color: #9ca3af; margin-bottom: 1rem;">This suite doesn't have any bookings yet. Start by creating your first booking!</p>
                    <button class="btn btn-primary" onclick="openBookingModal()">Create First Booking</button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Calendar Modal -->
<div id="calendar-modal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>{{ $suite->name }} - Calendar</h3>
            <div style="display: flex; gap: 1rem;">
                <button class="btn btn-success" onclick="openBookingModal()">+ New Booking</button>
                <button class="modal-close" onclick="closeModal('calendar-modal')">×</button>
            </div>
        </div>
        <div class="modal-body">
            <div style="display: flex; align-items: center; justify-content: center; gap: 2rem; margin-bottom: 1rem;">
                <button class="btn btn-secondary" onclick="prevMonth()">‹ Previous</button>
                <h3 id="calendar-month">Loading...</h3>
                <button class="btn btn-secondary" onclick="nextMonth()">Next ›</button>
            </div>
            
            <div id="calendar-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; margin-bottom: 1rem;">
                <!-- Calendar will be populated here -->
            </div>
            
            <div style="display: flex; justify-content: space-around; font-size: 14px;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 15px; height: 15px; background: #e8f5e8; border: 1px solid #4caf50; border-radius: 3px;"></div>
                    <span>Available</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 15px; height: 15px; background: #ffeaea; border: 1px solid #f44336; border-radius: 3px;"></div>
                    <span>Your Booking</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 15px; height: 15px; background: #fff3e0; border: 1px solid #ff9800; border-radius: 3px;"></div>
                    <span>Admin Booking</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div id="booking-modal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>New Booking for {{ $suite->name }}</h3>
            <button class="modal-close" onclick="closeModal('booking-modal')">×</button>
        </div>
        <form id="booking-form" onsubmit="saveBooking(event)">
            <input type="hidden" name="suite_id" value="{{ $suite->id }}">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="guest-name">Guest Name:</label>
                    <input type="text" id="guest-name" name="guest_name" required>
                </div>
                <div class="form-group">
                    <label for="guest-phone">Guest Phone:</label>
                    <input type="tel" id="guest-phone" name="guest_phone" required>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="check-in">Check-in Date:</label>
                    <input type="date" id="check-in" name="check_in" required>
                </div>
                <div class="form-group">
                    <label for="check-out">Check-out Date:</label>
                    <input type="date" id="check-out" name="check_out" required>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="guest-quantity">Number of Guests:</label>
                    <input type="number" id="guest-quantity" name="guest_quantity" min="1" max="{{ $suite->capacity_people }}" required>
                </div>
                <div class="form-group">
                    <label for="deposit-amount">Deposit Amount (€):</label>
                    <input type="number" id="deposit-amount" name="deposit_amount" step="0.01" min="0">
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
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
let calendarData = {};

function openCalendar() {
    openModal('calendar-modal');
    loadCalendar();
}

function openBookingModal() {
    openModal('booking-modal');
}

function loadCalendar() {
    const url = `{{ route('owner.suite.calendar', $suite) }}?month=${currentMonth + 1}&year=${currentYear}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            calendarData = data;
            renderCalendar();
        })
        .catch(error => {
            console.error('Error loading calendar:', error);
            showAlert('Error loading calendar', 'error');
        });
}

function renderCalendar() {
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                      'July', 'August', 'September', 'October', 'November', 'December'];
    
    document.getElementById('calendar-month').textContent = `${monthNames[currentMonth]} ${currentYear}`;
    
    const grid = document.getElementById('calendar-grid');
    grid.innerHTML = '';
    
    // Day headers
    const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    dayHeaders.forEach(day => {
        const header = document.createElement('div');
        header.style.cssText = 'text-align: center; font-weight: bold; padding: 0.5rem; background: #f0f0f0; font-size: 0.9rem;';
        header.textContent = day;
        grid.appendChild(header);
    });
    
    // Get first day of month and number of days
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    
    // Empty cells for days before month starts
    for (let i = 0; i < firstDay; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.style.cssText = 'aspect-ratio: 1; display: flex; align-items: center; justify-content: center; border: 1px solid #e0e0e0;';
        grid.appendChild(emptyDay);
    }
    
    // Days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = document.createElement('div');
        dayElement.style.cssText = 'aspect-ratio: 1; display: flex; align-items: center; justify-content: center; border: 1px solid #e0e0e0; cursor: pointer; font-size: 0.9rem; transition: all 0.2s; background: white;';
        dayElement.textContent = day;
        
        const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        
        // Check if there's a booking for this day
        let hasOwnerBooking = false;
        let hasAdminBooking = false;
        
        if (calendarData.owner_bookings && calendarData.owner_bookings[dateStr]) {
            hasOwnerBooking = true;
            dayElement.style.background = '#ffeaea';
            dayElement.style.borderColor = '#f44336';
            dayElement.title = `Your booking: ${calendarData.owner_bookings[dateStr].booking.guest_name}`;
        }
        
        if (calendarData.admin_bookings && calendarData.admin_bookings[dateStr]) {
            hasAdminBooking = true;
            dayElement.style.background = '#fff3e0';
            dayElement.style.borderColor = '#ff9800';
            dayElement.title = `Admin booking: ${calendarData.admin_bookings[dateStr].booking.guest_name}`;
            dayElement.style.cursor = 'not-allowed';
        }
        
        if (!hasOwnerBooking && !hasAdminBooking) {
            dayElement.style.background = '#e8f5e8';
            dayElement.style.borderColor = '#4caf50';
            dayElement.addEventListener('click', () => {
                document.getElementById('check-in').value = dateStr;
                openBookingModal();
            });
        }
        
        grid.appendChild(dayElement);
    }
}

function prevMonth() {
    if (currentMonth === 0) {
        currentMonth = 11;
        currentYear--;
    } else {
        currentMonth--;
    }
    loadCalendar();
}

function nextMonth() {
    if (currentMonth === 11) {
        currentMonth = 0;
        currentYear++;
    } else {
        currentMonth++;
    }
    loadCalendar();
}

function saveBooking(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('{{ route("owner.bookings.create") }}', {
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
            loadCalendar(); // Refresh calendar if open
            form.reset();
            // Refresh the page to update booking stats
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

function viewBookingDetails(bookingId) {
    fetch(`{{ route('owner.bookings.details', '') }}/${bookingId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const booking = data.booking;
                let message = `Guest: ${booking.guest_name}\n`;
                message += `Phone: ${booking.guest_phone}\n`;
                message += `Dates: ${booking.check_in} to ${booking.check_out}\n`;
                message += `Guests: ${booking.guest_quantity}\n`;
                if (booking.deposit_paid) {
                    message += `Deposit: €${booking.deposit_amount || 0}\n`;
                }
                if (booking.notes) {
                    message += `Notes: ${booking.notes}`;
                }
                alert(message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error loading booking details', 'error');
        });
}

// Set minimum date for check-in to today
document.getElementById('check-in').min = new Date().toISOString().split('T')[0];

// Update check-out minimum date when check-in changes
document.getElementById('check-in').addEventListener('change', function() {
    const checkIn = new Date(this.value);
    checkIn.setDate(checkIn.getDate() + 1);
    document.getElementById('check-out').min = checkIn.toISOString().split('T')[0];
});

// Set max guests based on suite capacity
document.getElementById('guest-quantity').max = {{ $suite->capacity_people }};
</script>
@endpush
@endsection