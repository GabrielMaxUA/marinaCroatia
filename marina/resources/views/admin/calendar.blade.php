@extends('layouts.app')

@section('title', 'Calendar View - Marina Croatia')

@section('content')
<div class="container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1>Booking Calendar</h1>
            <p style="color: #6b7280;">View and manage all bookings by date</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button class="btn btn-primary" onclick="openModal('booking-modal')">+ New Booking</button>
            <a href="{{ route('admin.bookings') }}" class="btn btn-secondary">← Back to Bookings</a>
        </div>
    </div>

    <!-- Calendar Filters -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <form method="GET" id="calendar-filters" style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
                <div class="form-group" style="margin: 0;">
                    <label for="year">Year:</label>
                    <select name="year" id="year" onchange="updateCalendar()">
                        @for($y = date('Y'); $y <= date('Y') + 2; $y++)
                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label for="month">Month:</label>
                    <select name="month" id="month" onchange="updateCalendar()">
                        @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label for="location_filter">Location:</label>
                    <select name="location_id" id="location_filter" onchange="updateCalendar()">
                        <option value="">All Locations</option>
                        @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Legend -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <h3>Legend</h3>
            <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 20px; height: 20px; background: #3b82f6; border-radius: 4px;"></div>
                    <span>Admin Bookings</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 20px; height: 20px; background: #10b981; border-radius: 4px;"></div>
                    <span>Owner Bookings</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 20px; height: 20px; background: #f59e0b; border-radius: 4px;"></div>
                    <span>Conflicts</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="card">
        <div class="card-header">
            <h3>{{ date('F Y', mktime(0, 0, 0, request('month', date('n')), 1, request('year', date('Y')))) }}</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="calendar-grid">
                <!-- Calendar headers -->
                <div class="calendar-header">
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="day-header">{{ $day }}</div>
                    @endforeach
                </div>
                
                <!-- Calendar days -->
                <div class="calendar-body">
                    @php
                        $year = request('year', date('Y'));
                        $month = request('month', date('n'));
                        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        $firstDayOfWeek = date('w', mktime(0, 0, 0, $month, 1, $year));
                    @endphp
                    
                    <!-- Empty cells for days before month start -->
                    @for($i = 0; $i < $firstDayOfWeek; $i++)
                        <div class="calendar-day empty"></div>
                    @endfor
                    
                    <!-- Days of the month -->
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                            $dayBookings = $bookings->where('booking_date', $currentDate);
                        @endphp
                        <div class="calendar-day {{ $currentDate == date('Y-m-d') ? 'today' : '' }}" 
                             onclick="viewDayBookings('{{ $currentDate }}')">
                            <div class="day-number">{{ $day }}</div>
                            @if($dayBookings->count() > 0)
                                <div class="booking-indicators">
                                    @foreach($dayBookings->groupBy('booking_source') as $source => $sourceBookings)
                                        <div class="booking-indicator {{ $source }}" 
                                             title="{{ ucfirst($source) }}: {{ $sourceBookings->count() }} booking(s)">
                                            {{ $sourceBookings->count() }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Day Bookings Modal -->
<div id="day-bookings-modal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3 id="day-bookings-title">Bookings for Date</h3>
            <button class="modal-close" onclick="closeModal('day-bookings-modal')">×</button>
        </div>
        <div class="modal-body" id="day-bookings-content">
            <!-- Day bookings will be loaded here -->
        </div>
    </div>
</div>

@push('styles')
<style>
    .calendar-grid {
        display: flex;
        flex-direction: column;
        width: 100%;
    }
    
    .calendar-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .day-header {
        padding: 1rem;
        text-align: center;
        font-weight: 600;
        color: #374151;
        border-right: 1px solid #e2e8f0;
    }
    
    .day-header:last-child {
        border-right: none;
    }
    
    .calendar-body {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        grid-auto-rows: 120px;
    }
    
    .calendar-day {
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.5rem;
        position: relative;
        cursor: pointer;
        transition: background-color 0.2s;
        display: flex;
        flex-direction: column;
    }
    
    .calendar-day:hover {
        background: #f8fafc;
    }
    
    .calendar-day:last-child {
        border-right: none;
    }
    
    .calendar-day.empty {
        background: #f9fafb;
        cursor: default;
    }
    
    .calendar-day.today {
        background: #eff6ff;
        border: 2px solid #3b82f6;
    }
    
    .day-number {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.25rem;
    }
    
    .booking-indicators {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        flex-grow: 1;
        align-items: flex-start;
    }
    
    .booking-indicator {
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
        min-width: 20px;
        text-align: center;
    }
    
    .booking-indicator.admin {
        background: #3b82f6;
    }
    
    .booking-indicator.owner {
        background: #10b981;
    }
    
    .booking-indicator.conflict {
        background: #f59e0b;
    }
    
    @media (max-width: 768px) {
        .calendar-body {
            grid-auto-rows: 80px;
        }
        
        .calendar-day {
            padding: 0.25rem;
        }
        
        .day-number {
            font-size: 0.875rem;
        }
        
        .booking-indicator {
            font-size: 0.625rem;
            padding: 0.125rem 0.25rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function updateCalendar() {
    const form = document.getElementById('calendar-filters');
    form.submit();
}

function viewDayBookings(date) {
    document.getElementById('day-bookings-title').textContent = `Bookings for ${date}`;
    
    // Load bookings for the specific date
    fetch(`/admin/calendar/day/${date}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let content = '';
                if (data.bookings.length > 0) {
                    content = '<div class="bookings-list">';
                    data.bookings.forEach(booking => {
                        const sourceClass = booking.booking_source === 'admin' ? 'admin' : 'owner';
                        content += `
                            <div class="booking-item ${sourceClass}">
                                <div class="booking-header">
                                    <h4>${booking.guest_name}</h4>
                                    <span class="booking-source ${sourceClass}">${booking.booking_source.toUpperCase()}</span>
                                </div>
                                <div class="booking-details">
                                    <p><strong>Suite:</strong> ${booking.suite_name} (${booking.house_name})</p>
                                    <p><strong>Phone:</strong> ${booking.guest_phone}</p>
                                    <p><strong>Guests:</strong> ${booking.guest_quantity}</p>
                                    <p><strong>Stay:</strong> ${booking.check_in} to ${booking.check_out} (${booking.total_nights} nights)</p>
                                    ${booking.notes ? `<p><strong>Notes:</strong> ${booking.notes}</p>` : ''}
                                </div>
                                <div class="booking-actions">
                                    <button class="btn btn-sm btn-secondary" onclick="editBooking(${booking.id})">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="cancelBooking(${booking.id})">Cancel</button>
                                </div>
                            </div>
                        `;
                    });
                    content += '</div>';
                } else {
                    content = '<p>No bookings for this date.</p>';
                }
                
                document.getElementById('day-bookings-content').innerHTML = content;
                openModal('day-bookings-modal');
            } else {
                showAlert('Error loading bookings for this date', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error loading bookings', 'error');
        });
}

function editBooking(bookingId) {
    // TODO: Implement booking edit functionality
    showAlert('Booking edit functionality coming soon!', 'warning');
}

function cancelBooking(bookingId) {
    if (confirm('Are you sure you want to cancel this booking?')) {
        // TODO: Implement booking cancellation
        showAlert('Booking cancellation functionality coming soon!', 'warning');
    }
}
</script>
@endpush
@endsection