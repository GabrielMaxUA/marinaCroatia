<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Location;
use App\Models\House;
use App\Models\Suite;
use App\Models\Booking;
use App\Models\BookingDate;
use App\Models\SiteContent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }
        $locations = Location::with('houses.suites')->get();
        $mainHeading = SiteContent::get('main_heading', 'Luxury Croatian Accommodations');
        $mainDescription = SiteContent::get('main_description', 'We are a premium travel agency specializing in exclusive accommodations along the Croatian coast.');
        
        return view('admin.dashboard', compact('locations', 'mainHeading', 'mainDescription'));
    }

    public function houses(Request $request)
    {
        $query = House::with(['location', 'owner', 'suites', 'images']);
        
        // Apply filters
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('street_address', 'LIKE', '%' . $request->search . '%');
            });
        }
        
        if ($request->location_id) {
            $query->where('location_id', $request->location_id);
        }
        
        if ($request->owner_id) {
            $query->where('owner_id', $request->owner_id);
        }
        
        $houses = $query->paginate(12);
        $locations = Location::all();
        $owners = User::where('role', 'owner')->get();
        
        return view('admin.houses', compact('houses', 'locations', 'owners'));
    }

    public function owners(Request $request)
    {
        $query = User::where('role', 'owner')
                    ->with(['houses.location', 'houses.suites']);
        
        // Apply filters
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('email', 'LIKE', '%' . $request->search . '%');
            });
        }
        
        if ($request->location_id) {
            $query->whereHas('houses', function($q) use ($request) {
                $q->where('location_id', $request->location_id);
            });
        }
        
        if ($request->status) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }
        
        $owners = $query->paginate(12);
        $locations = Location::all();
        
        return view('admin.owners', compact('owners', 'locations'));
    }

    public function createOwner(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
        ]);

        $tempPassword = 'temp' . rand(1000, 9999);

        $owner = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password_hash' => Hash::make($tempPassword),
            'temp_password' => $tempPassword, // Store for admin viewing
            'role' => 'owner',
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'owner' => $owner,
            'temporary_password' => $tempPassword,
            'message' => 'Owner created successfully'
        ]);
    }

    public function updateOwner(Request $request, User $owner)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $owner->id,
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean'
        ]);

        $owner->update($request->only([
            'first_name', 'last_name', 'email', 'phone', 'is_active'
        ]));

        return response()->json([
            'success' => true,
            'owner' => $owner,
            'message' => 'Owner updated successfully'
        ]);
    }

    public function resetOwnerPassword(User $owner)
    {
        $tempPassword = 'temp' . rand(1000, 9999);
        
        $owner->update([
            'password_hash' => Hash::make($tempPassword),
            'temp_password' => $tempPassword // Store for admin viewing
        ]);

        return response()->json([
            'success' => true,
            'temporary_password' => $tempPassword,
            'message' => 'Password reset successfully'
        ]);
    }

    public function createLocation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:locations,name',
            'description' => 'nullable|string'
        ]);

        $location = Location::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'location' => $location,
            'message' => 'Location created successfully'
        ]);
    }

    public function updateLocation(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:locations,name,' . $location->id,
            'description' => 'nullable|string'
        ]);

        $location->update($request->only(['name', 'description']));

        return response()->json([
            'success' => true,
            'location' => $location,
            'message' => 'Location updated successfully'
        ]);
    }

    public function deleteLocation(Location $location)
    {
        DB::beginTransaction();
        try {
            $location->houses()->delete();
            $location->delete();
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Location deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting location'
            ], 500);
        }
    }

    public function createHouse(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'owner_id' => 'required|exists:users,id',
            'name' => 'required|string|max:150',
            'street_address' => 'required|string|max:200',
            'house_number' => 'nullable|string|max:10',
            'distance_to_sea' => 'nullable|string|max:50',
            'parking_available' => 'boolean',
            'parking_description' => 'nullable|string',
            'description' => 'nullable|string',
            'owner_phone' => 'nullable|string|max:20',
            'owner_email' => 'nullable|email',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
        ]);

        $house = House::create($request->all());

        return response()->json([
            'success' => true,
            'house' => $house->load(['location', 'owner']),
            'message' => 'House created successfully'
        ]);
    }

    public function updateHouse(Request $request, House $house)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'owner_id' => 'required|exists:users,id',
            'name' => 'required|string|max:150',
            'street_address' => 'required|string|max:200',
            'house_number' => 'nullable|string|max:10',
            'distance_to_sea' => 'nullable|string|max:50',
            'parking_available' => 'boolean',
            'parking_description' => 'nullable|string',
            'description' => 'nullable|string',
            'owner_phone' => 'nullable|string|max:20',
            'owner_email' => 'nullable|email',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'is_active' => 'boolean'
        ]);

        $house->update($request->all());

        return response()->json([
            'success' => true,
            'house' => $house->load(['location', 'owner']),
            'message' => 'House updated successfully'
        ]);
    }

    public function deleteHouse(House $house)
    {
        DB::beginTransaction();
        try {
            $house->suites()->delete();
            $house->delete();
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'House deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting house'
            ], 500);
        }
    }

    public function createSuite(Request $request)
    {
        $request->validate([
            'house_id' => 'required|exists:houses,id',
            'name' => 'required|string|max:100',
            'capacity_people' => 'required|integer|min:1|max:20',
            'bedrooms' => 'required|integer|min:0|max:10',
            'bathrooms' => 'required|integer|min:0|max:10',
            'floor_number' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        $suite = Suite::create($request->all());

        return response()->json([
            'success' => true,
            'suite' => $suite->load('house'),
            'message' => 'Suite created successfully'
        ]);
    }

    public function updateSuite(Request $request, Suite $suite)
    {
        $request->validate([
            'house_id' => 'required|exists:houses,id',
            'name' => 'required|string|max:100',
            'capacity_people' => 'required|integer|min:1|max:20',
            'bedrooms' => 'required|integer|min:0|max:10',
            'bathrooms' => 'required|integer|min:0|max:10',
            'floor_number' => 'nullable|integer',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $suite->update($request->all());

        return response()->json([
            'success' => true,
            'suite' => $suite->load('house'),
            'message' => 'Suite updated successfully'
        ]);
    }

    public function deleteSuite(Suite $suite)
    {
        $suite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Suite deleted successfully'
        ]);
    }

    public function updateSiteContent(Request $request)
    {
        $request->validate([
            'main_heading' => 'required|string|max:200',
            'main_description' => 'required|string|max:1000',
        ]);

        SiteContent::set('main_heading', $request->main_heading, Auth::id());
        SiteContent::set('main_description', $request->main_description, Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Site content updated successfully'
        ]);
    }

    public function bookings(Request $request)
    {
        $query = Booking::with(['suite.house.location', 'creator']);

        // Filter by booking source (admin/owner)
        if ($request->source && $request->source !== 'all') {
            if ($request->source === 'admin') {
                $query->where('is_owner_booking', false);
            } else {
                $query->where('is_owner_booking', true);
            }
        }

        // Filter by location
        if ($request->location_id) {
            $query->whereHas('suite.house', function($q) use ($request) {
                $q->where('location_id', $request->location_id);
            });
        }

        // Filter by owner
        if ($request->owner_id) {
            $query->whereHas('suite.house', function($q) use ($request) {
                $q->where('owner_id', $request->owner_id);
            });
        }

        // Filter by house
        if ($request->house_id) {
            $query->whereHas('suite', function($q) use ($request) {
                $q->where('house_id', $request->house_id);
            });
        }

        // Filter by suite
        if ($request->suite_id) {
            $query->where('suite_id', $request->suite_id);
        }

        // Filter by status
        if ($request->status) {
            switch($request->status) {
                case 'active':
                    $query->whereNull('cancelled_at')
                          ->where('check_out', '>=', now());
                    break;
                case 'completed':
                    $query->whereNull('cancelled_at')
                          ->where('check_out', '<', now());
                    break;
                case 'cancelled':
                    $query->whereNotNull('cancelled_at');
                    break;
                case 'upcoming':
                    $query->whereNull('cancelled_at')
                          ->where('check_in', '>', now());
                    break;
            }
        }

        $bookings = $query->orderBy('check_in', 'desc')->paginate(20);
        $locations = Location::all();
        $owners = User::where('role', 'owner')->get();
        $houses = House::with('location')->get();
        $suites = Suite::with('house')->get();

        return view('admin.bookings', compact('bookings', 'locations', 'owners', 'houses', 'suites'));
    }

    public function createBooking(Request $request)
    {
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

        $checkIn = new \DateTime($request->check_in);
        $checkOut = new \DateTime($request->check_out);
        $totalNights = $checkIn->diff($checkOut)->days;

        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'suite_id' => $request->suite_id,
                'created_by' => Auth::id(),
                'booking_source' => 'admin',
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
                'is_owner_booking' => false,
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
        if ($booking->is_owner_booking && !$request->has('force_admin_edit')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit owner bookings'
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
        if ($booking->is_owner_booking) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel owner bookings'
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

    // Suite management methods
    public function getHouseSuites(House $house)
    {
        $suites = $house->suites()->with('amenities')->get();
        
        return response()->json([
            'success' => true,
            'suites' => $suites
        ]);
    }

    public function getSuite(Suite $suite)
    {
        return response()->json([
            'success' => true,
            'suite' => $suite
        ]);
    }
}