@extends('layouts.app')

@section('title', 'Owner Dashboard - Marina Croatia')

@section('content')
<div class="container">
    <!-- Dashboard Stats -->
    <div class="grid grid-3" style="margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #3b82f6; font-size: 2rem; margin-bottom: 0.5rem;">{{ $houses->count() }}</h3>
                <p style="margin: 0; font-weight: 500;">Your Properties</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #10b981; font-size: 2rem; margin-bottom: 0.5rem;">{{ $totalBookings }}</h3>
                <p style="margin: 0; font-weight: 500;">Total Bookings</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #f59e0b; font-size: 2rem; margin-bottom: 0.5rem;">{{ $activeBookings }}</h3>
                <p style="margin: 0; font-weight: 500;">Active Bookings</p>
            </div>
        </div>
    </div>

    <!-- Welcome Message -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <h2 style="margin-bottom: 1rem;">Welcome, {{ auth()->user()->full_name }}!</h2>
            <p>Manage your properties and bookings below. You can view your houses, suites, and handle booking requests for your accommodations.</p>
        </div>
    </div>

    <!-- Properties Section -->
    <div style="margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem;">Your Properties</h2>
        @if($houses->count() > 0)
            <div class="grid grid-2">
                @foreach($houses as $house)
                <div class="card">
                    <div class="card-header">
                        <h3>{{ $house->name }}</h3>
                        <span class="badge" style="background: #e5e7eb; color: #374151; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                            {{ $house->location->name }}
                        </span>
                    </div>
                    <div class="card-body">
                        <p><strong>Address:</strong> {{ $house->street_address }}{{ $house->house_number ? ' ' . $house->house_number : '' }}</p>
                        @if($house->distance_to_sea)
                        <p><strong>Distance to sea:</strong> {{ $house->distance_to_sea }}</p>
                        @endif
                        @if($house->description)
                        <p>{{ Str::limit($house->description, 100) }}</p>
                        @endif
                        
                        <div style="margin-top: 1rem;">
                            <p><strong>Suites:</strong> {{ $house->suites->count() }}</p>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 1rem;">
                                <a href="{{ route('owner.house', $house) }}" class="btn btn-primary">View Details</a>
                                @if($house->suites->count() > 0)
                                <a href="{{ route('owner.bookings', ['house_id' => $house->id]) }}" class="btn btn-secondary">
                                    Bookings ({{ $house->suites->sum(function($suite) { return $suite->bookings()->where('is_owner_booking', true)->whereNull('cancelled_at')->count(); }) }})
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem;">
                    <h3 style="color: #6b7280; margin-bottom: 1rem;">No Properties Assigned</h3>
                    <p style="color: #9ca3af;">You don't have any properties assigned to you yet. Please contact the administrator to get properties assigned to your account.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Recent Bookings -->
    @if($houses->count() > 0)
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3>Recent Bookings</h3>
            <a href="{{ route('owner.bookings') }}" class="btn btn-primary">View All Bookings</a>
        </div>
        <div class="card-body">
            @php
                $recentBookings = collect();
                foreach($houses as $house) {
                    foreach($house->suites as $suite) {
                        $suiteBooings = $suite->bookings()->where('is_owner_booking', true)
                                          ->whereNull('cancelled_at')
                                          ->latest()
                                          ->limit(3)
                                          ->with(['suite.house'])
                                          ->get();
                        $recentBookings = $recentBookings->merge($suiteBooings);
                    }
                }
                $recentBookings = $recentBookings->sortByDesc('created_at')->take(5);
            @endphp

            @if($recentBookings->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Guest</th>
                                <th>Property</th>
                                <th>Suite</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Guests</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBookings as $booking)
                            <tr>
                                <td>{{ $booking->guest_name }}</td>
                                <td>{{ $booking->suite->house->name }}</td>
                                <td>{{ $booking->suite->name }}</td>
                                <td>{{ $booking->check_in->format('M d, Y') }}</td>
                                <td>{{ $booking->check_out->format('M d, Y') }}</td>
                                <td>{{ $booking->guest_quantity }}</td>
                                <td>
                                    @if($booking->check_out < now())
                                        <span class="badge" style="background: #6b7280; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Completed</span>
                                    @elseif($booking->check_in <= now() && $booking->check_out >= now())
                                        <span class="badge" style="background: #10b981; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Active</span>
                                    @else
                                        <span class="badge" style="background: #3b82f6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Upcoming</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="text-align: center; color: #6b7280; padding: 2rem;">No bookings yet. Start managing your calendar to receive bookings!</p>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection