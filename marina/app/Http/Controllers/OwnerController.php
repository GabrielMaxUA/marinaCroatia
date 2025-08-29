<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;
use App\Models\Suite;
use App\Models\Booking;
use App\Models\BookingDate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OwnerController extends Controller
{
    // Constructor removed - auth handled by route middleware

    private function checkOwnerAccess()
    {
        if (!Auth::user() || !Auth::user()->isOwner()) {
            abort(403, 'Access denied. Owner privileges required.');
        }
    }

    public function dashboard()
    {
        $this->checkOwnerAccess();
        
        $owner = Auth::user();
        $houses = $owner->houses()
                       ->with(['location', 'suites.bookings'])
                       ->where('is_active', true)
                       ->get();

        $totalBookings = 0;
        $activeBookings = 0;
        
        foreach ($houses as $house) {
            foreach ($house->suites as $suite) {
                $totalBookings += $suite->bookings()->where('is_owner_booking', true)->count();
                $activeBookings += $suite->bookings()
                    ->where('is_owner_booking', true)
                    ->whereNull('cancelled_at')
                    ->where('check_out', '>=', now())
                    ->count();
            }
        }

        return redirect()->route('home');
    }

    public function house(House $house)
    {
        $this->checkOwnerAccess();
        
        if ($house->owner_id !== Auth::id()) {
            abort(403, 'Access denied. You can only view your own properties.');
        }

        $house->load(['location', 'suites.bookings']);
        
        return view('owner.house', compact('house'));
    }

    public function suite(Suite $suite)
    {
        $this->checkOwnerAccess();
        
        if ($suite->house->owner_id !== Auth::id()) {
            abort(403, 'Access denied. You can only view your own properties.');
        }

        $suite->load(['house.location']);
        
        return view('owner.suite', compact('suite'));
    }

    public function calendar(Suite $suite, Request $request)
    {
        $this->checkOwnerAccess();
        
        if ($suite->house->owner_id !== Auth::id()) {
            abort(403, 'Access denied. You can only view your own properties.');
        }

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $ownerBookings = BookingDate::where('suite_id', $suite->id)
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->with(['booking' => function($query) {
                $query->where('is_owner_booking', true);
            }])
            ->get()
            ->keyBy('booking_date');

        $adminBookings = BookingDate::where('suite_id', $suite->id)
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->with(['booking' => function($query) {
                $query->where('is_owner_booking', false);
            }])
            ->get()
            ->keyBy('booking_date');

        return response()->json([
            'owner_bookings' => $ownerBookings,
            'admin_bookings' => $adminBookings,
            'month' => $month,
            'year' => $year
        ]);
    }

    public function bookings(Request $request)
    {
        $this->checkOwnerAccess();
        
        $owner = Auth::user();
        
        $query = Booking::whereHas('suite.house', function($q) use ($owner) {
                    $q->where('owner_id', $owner->id);
                })
                ->where('is_owner_booking', true)
                ->with(['suite.house.location']);

        if ($request->house_id) {
            $query->whereHas('suite', function($q) use ($request) {
                $q->where('house_id', $request->house_id);
            });
        }

        if ($request->suite_id) {
            $query->where('suite_id', $request->suite_id);
        }

        $bookings = $query->orderBy('check_in', 'desc')->paginate(20);

        return view('owner.bookings', compact('bookings'));
    }

    public function createBooking(Request $request)
    {
        $this->checkOwnerAccess();
        
        $request->validate([
            'suite_id' => 'required|exists:suites,id',
            'guest_name' => 'required|string|max:200',
            'guest_phone' => 'required|string|max:20',
            'guest_quantity' => 'required|integer|min:1',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'parking_needed' => 'boolean',
            'pets_allowed' => 'boolean',
            'has_small_kids' => 'boolean',
            'deposit_paid' => 'boolean',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $suite = Suite::findOrFail($request->suite_id);
        
        if ($suite->house->owner_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You can only create bookings for your own properties.'
            ], 403);
        }

        $checkIn = new \DateTime($request->check_in);
        $checkOut = new \DateTime($request->check_out);
        $totalNights = $checkIn->diff($checkOut)->days;

        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'suite_id' => $request->suite_id,
                'created_by' => Auth::id(),
                'booking_source' => 'owner',
                'guest_name' => $request->guest_name,
                'guest_phone' => $request->guest_phone,
                'guest_quantity' => $request->guest_quantity,
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'total_nights' => $totalNights,
                'parking_needed' => $request->boolean('parking_needed'),
                'pets_allowed' => $request->boolean('pets_allowed'),
                'has_small_kids' => $request->boolean('has_small_kids'),
                'deposit_paid' => $request->boolean('deposit_paid'),
                'deposit_amount' => $request->deposit_amount,
                'notes' => $request->notes,
                'is_owner_booking' => true,
            ]);

            // Create BookingDate records for each day of the booking
            $current = clone $checkIn;
            while ($current < $checkOut) {
                BookingDate::create([
                    'booking_id' => $booking->id,
                    'suite_id' => $request->suite_id,
                    'booking_date' => $current->format('Y-m-d')
                ]);
                $current->addDay();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'booking' => $booking->load(['suite', 'creator']),
                'message' => 'Booking created successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateBooking(Request $request, Booking $booking)
    {
        $this->checkOwnerAccess();
        
        if (!$booking->is_owner_booking || $booking->suite->house->owner_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You can only edit your own bookings.'
            ], 403);
        }

        $request->validate([
            'suite_id' => 'required|exists:suites,id',
            'guest_name' => 'required|string|max:200',
            'guest_phone' => 'required|string|max:20',
            'guest_quantity' => 'required|integer|min:1',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'parking_needed' => 'boolean',
            'pets_allowed' => 'boolean',
            'has_small_kids' => 'boolean',
            'deposit_paid' => 'boolean',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $checkIn = new \DateTime($request->check_in);
        $checkOut = new \DateTime($request->check_out);
        $totalNights = $checkIn->diff($checkOut)->days;

        $booking->update([
            'suite_id' => $request->suite_id,
            'guest_name' => $request->guest_name,
            'guest_phone' => $request->guest_phone,
            'guest_quantity' => $request->guest_quantity,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'total_nights' => $totalNights,
            'parking_needed' => $request->boolean('parking_needed'),
            'pets_allowed' => $request->boolean('pets_allowed'),
            'has_small_kids' => $request->boolean('has_small_kids'),
            'deposit_paid' => $request->boolean('deposit_paid'),
            'deposit_amount' => $request->deposit_amount,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'booking' => $booking->load(['suite', 'creator']),
            'message' => 'Booking updated successfully'
        ]);
    }

    public function cancelBooking(Booking $booking)
    {
        $this->checkOwnerAccess();
        
        if (!$booking->is_owner_booking || $booking->suite->house->owner_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You can only cancel your own bookings.'
            ], 403);
        }

        $booking->update([
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully'
        ]);
    }

    public function bookingDetails(Booking $booking)
    {
        $this->checkOwnerAccess();
        
        if ($booking->suite->house->owner_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'booking' => $booking->load(['suite.house.location', 'creator']),
            'can_edit' => $booking->is_owner_booking,
            'source_label' => $booking->is_owner_booking ? 'Owner Managed' : 'Admin Managed'
        ]);
    }

    public function profile()
    {
        $this->checkOwnerAccess();
        
        return view('owner.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $this->checkOwnerAccess();
        
        $user = Auth::user();
        
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'notification_email' => 'boolean',
            'notification_sms' => 'boolean',
            'preferred_contact_time' => 'nullable|string|max:50',
        ]);

        $user->update($request->only([
            'first_name', 'last_name', 'phone', 
            'notification_email', 'notification_sms', 'preferred_contact_time'
        ]));

        return back()->with('success', 'Profile updated successfully.');
    }
}