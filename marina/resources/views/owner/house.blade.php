@extends('layouts.app')

@section('title', $house->name . ' - Marina Croatia')

@section('content')
<div class="container">
    <!-- Breadcrumb -->
    <!-- <nav style="margin-bottom: 2rem;">
        <a href="{{ route('home') }}" style="color: #3b82f6; text-decoration: none;">← Back to Home</a>
    </nav> -->

    <!-- House Details -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h1>{{ $house->name }}</h1>
            <span class="badge" style="background: #3b82f6; color: white; padding: 6px 12px; border-radius: 6px;">
                {{ $house->location->name }}
            </span>
        </div>
        <div class="card-body">
            <div class="grid grid-2">
                <div>
                    <h3 style="margin-bottom: 1rem;">Property Details</h3>
                    <p><strong>Address:</strong> {{ $house->street_address }}{{ $house->house_number ? ' ' . $house->house_number : '' }}</p>
                    @if($house->distance_to_sea)
                    <p><strong>Distance to sea:</strong> {{ $house->distance_to_sea }}</p>
                    @endif
                    @if($house->parking_available)
                    <p><strong>Parking:</strong> Available{{ $house->parking_description ? ' - ' . $house->parking_description : '' }}</p>
                    @else
                    <p><strong>Parking:</strong> Not available</p>
                    @endif
                    @if($house->description)
                    <p><strong>Description:</strong> {{ $house->description }}</p>
                    @endif
                </div>
                <div>
                    <h3 style="margin-bottom: 1rem;">Contact Information</h3>
                    @if($house->owner_phone)
                    <p><strong>Phone:</strong> {{ $house->owner_phone }}</p>
                    @endif
                    @if($house->owner_email)
                    <p><strong>Email:</strong> {{ $house->owner_email }}</p>
                    @endif
                    @if($house->bank_account_number)
                    <p><strong>Bank Account:</strong> {{ $house->bank_account_number }}</p>
                    @endif
                    @if($house->bank_name)
                    <p><strong>Bank:</strong> {{ $house->bank_name }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Suites Section -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Suites ({{ $house->suites->count() }})</h2>
            <a href="{{ route('owner.bookings', ['house_id' => $house->id]) }}" class="btn btn-primary">
                View All Bookings for This Property
            </a>
        </div>

        @if($house->suites->count() > 0)
            <div class="grid grid-2">
                @foreach($house->suites as $suite)
                <div class="card">
                    <div class="card-header">
                        <h3>{{ $suite->name }}</h3>
                        @if($suite->floor_number)
                        <span class="badge" style="background: #e5e7eb; color: #374151; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                            Floor {{ $suite->floor_number }}
                        </span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
                            <span style="background: #f3f4f6; padding: 4px 8px; border-radius: 4px; font-size: 14px;">
                                👥 {{ $suite->capacity_people }} people
                            </span>
                            <span style="background: #f3f4f6; padding: 4px 8px; border-radius: 4px; font-size: 14px;">
                                🛏️ {{ $suite->bedrooms }} bedrooms
                            </span>
                            <span style="background: #f3f4f6; padding: 4px 8px; border-radius: 4px; font-size: 14px;">
                                🚿 {{ $suite->bathrooms }} bathrooms
                            </span>
                        </div>
                        
                        @if($suite->description)
                        <p style="margin-bottom: 1rem;">{{ $suite->description }}</p>
                        @endif

                        @php
                            $totalBookings = $suite->bookings()->where('is_owner_booking', true)->whereNull('cancelled_at')->count();
                            $activeBookings = $suite->bookings()
                                ->where('is_owner_booking', true)
                                ->whereNull('cancelled_at')
                                ->where('check_out', '>=', now())
                                ->count();
                        @endphp

                        <div style="margin-bottom: 1rem;">
                            <p><strong>Bookings:</strong> {{ $totalBookings }} total, {{ $activeBookings }} active</p>
                        </div>

                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <a href="{{ route('owner.suite', $suite) }}" class="btn btn-primary">View Details</a>
                            <button class="btn btn-success" onclick="openCalendar({{ $suite->id }}, '{{ $suite->name }}')">
                                Manage Calendar
                            </button>
                            <a href="{{ route('owner.bookings', ['suite_id' => $suite->id]) }}" class="btn btn-secondary">
                                Bookings ({{ $totalBookings }})
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem;">
                    <h3 style="color: #6b7280; margin-bottom: 1rem;">No Suites Available</h3>
                    <p style="color: #9ca3af;">This property doesn't have any suites configured yet. Please contact the administrator to add suites to this property.</p>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Calendar Modal -->
<div id="calendar-modal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3 id="calendar-room-title">Suite Calendar</h3>
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
            <h3>New Booking</h3>
            <button class="modal-close" onclick="closeModal('booking-modal')">×</button>
        </div>
        <form id="booking-form" onsubmit="saveBooking(event)">
            <input type="hidden" id="booking-suite-id" name="suite_id">
            
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
                    <input type="number" id="guest-quantity" name="guest_quantity" min="1" required>
                </div>
                <div class="form-group">
                    <label for="deposit-amount">Deposit Amount (€):</label>
                    <input type="number" id="deposit-amount" name="deposit_amount" step="0.01" min="0">
                </div>
                <div class="form-group" style="display: flex; align-items: end; gap: 1rem; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="parking_needed">
                        Parking Needed
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="pets_allowed">
                        Pets
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="has_small_kids">
                        Small Kids
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="deposit_paid">
                        Deposit Paid
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="booking-notes">Notes (optional):</label>
                <textarea id="booking-notes" name="notes" rows="3"></textarea>
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
let currentSuiteId = null;
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
let calendarData = {};

function openCalendar(suiteId, suiteName) {
    currentSuiteId = suiteId;
    document.getElementById('calendar-room-title').textContent = `${suiteName} - Calendar Management`;
    openModal('calendar-modal');
    loadCalendar();
}

function openBookingModal() {
    if (!currentSuiteId) return;
    document.getElementById('booking-suite-id').value = currentSuiteId;
    openModal('booking-modal');
}

function loadCalendar() {
    if (!currentSuiteId) return;
    
    const url = `{{ url('owner/suites') }}/${currentSuiteId}/calendar?month=${currentMonth + 1}&year=${currentYear}`;
    
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
        } else if (hasOwnerBooking) {
            dayElement.addEventListener('click', () => {
                // Show booking details or edit
                showAlert('Booking management coming soon!', 'info');
            });
        }
        
        dayElement.addEventListener('mouseenter', () => {
            if (!hasAdminBooking) {
                dayElement.style.transform = 'scale(1.05)';
            }
        });
        
        dayElement.addEventListener('mouseleave', () => {
            dayElement.style.transform = 'scale(1)';
        });
        
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
            loadCalendar(); // Refresh calendar
            form.reset();
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
document.getElementById('check-in').min = new Date().toISOString().split('T')[0];

// Update check-out minimum date when check-in changes
document.getElementById('check-in').addEventListener('change', function() {
    const checkIn = new Date(this.value);
    checkIn.setDate(checkIn.getDate() + 1);
    document.getElementById('check-out').min = checkIn.toISOString().split('T')[0];
});
</script>
@endpush
@endsection