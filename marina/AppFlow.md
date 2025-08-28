# Marina Croatia - Admin & Owner User Flows

## ADMIN WORKFLOW (Full System Control)

### 1. **Initial System Setup**
**Login**: Admin logs in with master credentials
- Access: Full system dashboard
- View: All locations, properties, owners, bookings

### 2. **Owner Management**
**Create New Owner Account:**
1. Navigate to "Owner Management" section
2. Click "Add New Owner"
3. Fill form:
   - First Name, Last Name
   - Email (becomes username)
   - Generate/Set temporary password
   - Phone number
   - Role: automatically set to "owner"
4. Send credentials to owner via email/phone
5. Owner appears in owners list

**Manage Existing Owners:**
- View all owners with their property counts
- Edit owner contact information
- Deactivate/reactivate owner accounts
- Reset owner passwords

### 3. **Location Management**
**Add New Location:**
1. Click "Add Location" 
2. Enter location name (e.g., "Brela", "Split")
3. Optional description
4. Save - location becomes available for house assignment

**Edit/Delete Locations:**
- Modify location names and descriptions
- Delete locations (warns if houses exist there)

### 4. **House Management (For Any Owner)**
**Add New House:**
1. Select "Add House" from any location
2. Fill comprehensive form:
   - House name
   - Street address + house number
   - Distance to sea
   - Parking availability + description
   - General description
   - **Assign to Owner** (dropdown of all owners)
   - Owner contact details (phone, email)
   - Banking information (account number, bank name)
3. Upload house images (multiple files)
4. Set primary image for listings
5. Save - house appears under selected location and owner

**Edit Houses:**
- Modify all house details
- Add/remove/reorder house images
- Change house ownership (reassign to different owner)
- Deactivate houses

### 5. **Suite Management (For Any House)**
**Add New Suite:**
1. Navigate: Location → House → "Add Suite"
2. Fill suite form:
   - Suite name/number
   - Capacity (max people)
   - Bedrooms count
   - Bathrooms count
   - Floor number
   - Description
3. Add amenities (dishwasher, washing machine, etc.)
4. Upload suite images (multiple)
5. Set primary suite image
6. Save - suite becomes bookable

**Edit Suites:**
- Modify suite details and amenities
- Add/remove/reorder suite images
- Deactivate suites

### 6. **Booking Overview & Management**
**Calendar Dashboard:**
1. Navigate: Owners → Select Owner → View Houses → Select House → View Suites
2. Click any suite to see calendar
3. **Calendar shows:**
   - Owner bookings (read-only, marked "Owner Managed")
   - Admin bookings (full control)
   - Available dates

**Create Admin Booking:**
1. Click available date on calendar
2. Fill booking form:
   - Guest name
   - Guest phone
   - Number of guests
   - Check-in/Check-out dates
   - Parking needed (checkbox)
   - Pets allowed (checkbox)
   - Small kids (checkbox)
   - Deposit paid (checkbox) + amount
   - Notes
3. Save - booking appears on calendar

**Manage Admin Bookings:**
- View booking details
- Edit/cancel only admin-created bookings
- Cannot modify owner bookings (view-only)

### 7. **Content Management**
**Edit Main Site Content:**
- Modify main page heading
- Update site description
- Manage general site information

### 8. **System Administration**
- Export all data (JSON backup)
- Import data from backup
- View activity logs
- Monitor system usage

---

## OWNER WORKFLOW (Limited to Own Properties)

### 1. **Owner Login**
**First Login:**
1. Receive credentials from admin
2. Login with provided email/password
3. Forced password change on first login
4. Access personal dashboard

### 2. **Owner Dashboard Overview**
**Upon Login See:**
- List of their houses only
- Quick booking summary
- Recent booking activity
- Navigation limited to their properties

### 3. **Property Viewing (Read-Only)**
**View Houses:**
- See all houses assigned to them by admin
- View house details, images, location
- **Cannot edit house information**
- **Cannot add new houses**

**View Suites:**
- Navigate: My Houses → Select House → View Suites
- See all suites in their houses
- View suite details, amenities, images
- **Cannot edit suite information**
- **Cannot add new suites**

### 4. **Booking Management (Only Capability)**
**View Suite Calendar:**
1. Navigate: My Houses → House → Suite → Calendar
2. **Calendar shows:**
   - Their own bookings (full control)
   - Admin bookings (read-only, marked "Admin Managed")
   - Available dates

**Create New Booking:**
1. Click available date
2. Fill booking form:
   - Guest name (required)
   - Guest phone (required)
   - Number of guests (required)
   - Check-in date (required)
   - Check-out date (required)
   - Parking needed (checkbox)
   - Pets (checkbox)
   - Small kids (checkbox)
   - Deposit paid (checkbox) + amount
   - Notes (optional)
3. Save - booking marked as "Owner Managed"

**Manage Own Bookings:**
- View their booking details
- Edit their own bookings only
- Cancel their own bookings only
- **Cannot touch admin bookings**

### 5. **Restrictions & Limitations**
**Cannot Access:**
- Other owners' properties
- System administration
- User management
- Location/house/suite creation or editing
- Site content management
- Admin bookings modification

**Can Only:**
- View their assigned properties
- Manage bookings for their properties
- Update their own profile/password

---

## SYSTEM INTERACTION FLOWS

### **Owner Creation Process:**
1. Admin creates owner account
2. System generates temporary password
3. Admin provides credentials to owner
4. Owner logs in and changes password
5. Admin assigns houses to owner
6. Owner can immediately start managing bookings

### **Property Assignment Flow:**
1. Admin creates location (if new)
2. Admin creates house and assigns to specific owner
3. Admin creates suites within house
4. Owner receives access to manage bookings for those suites
5. Both admin and owner see bookings on shared calendar (with permission restrictions)

### **Booking Conflict Prevention:**
1. System prevents double-booking same dates
2. Calendar shows all bookings for coordination
3. Admin sees "Owner Managed" vs "Admin Managed" labels
4. Conflicts resolved through communication, not system override

### **Data Security & Permissions:**
- Owners see only their assigned properties
- Admin sees everything but respects booking ownership
- All actions logged for accountability
- Database enforces permission boundaries


LOGIC DEPENDING ON USER TYPE:
parent blade:
header containing the company name center and login icon to the right corner of the header
admin when logged in will have addintional links:

owners -> will lead to owner list blade where admin can add/edit/remove owner from the list. when adding a new owner modal to be shown to enter all necessary owner data like name email name password (like in existing blade i have in the app). owners balde will be also with filter for better search like location/name

houses -> open grid of house cards where admin can filter them and edit bu clicking button(add to card) edit where admin can add/update.delete images/video of the house and edit house info

bookings -> will lead to booking blade with calendar to see all bookings from each owner and house and unit. filter must be there as well so admin can filter by house. 
clicking on booked date modal/new blade to be opened with all bookings for this date (unles it was filtered by admin so it would show bookings for filtered item)





 REGULAR USER - NO LIGIN REQUIERED, ONLY VIEWS CONTENT:
 1. MAIN PAGE - SEEING WELCOME TITLE AND LIST OF LOCATIONS (loations are presented as an image cart with the name of the location 300x300 size card)
 2. clicking on location card -> new blade opens with grid of houses in this specific location (same presentation -> card 250 by 250 where images of this house in top of the card and description of the house like distance to the sea, pets allowance, parking etc)
 3. clicking on the house card inside the house grid blade user directed to the new blade with new grid of all units in this house (same style as a house grid where images/video of the unit is top and below the image is a discription of the unit like number of guest this unit can take number of bedrooms and bathrooms and what amenities this unit has)


SUPER ADMIN - LIGIN REQUIERED, CREATING/READING/UPDATING/DELETING CONTENT:
 1. MAIN PAGE - SEEING WELCOME TITLE AND LIST OF LOCATIONS (loations are presented as an image cart with the name of the location 300width with auto height size card)
 EDIT BUTTON FOR EACH COMPONENT LIKE TITLE/LOCATION to be able to add ne location delete or update existing location, change photo for location card so as the title text - save button to update and reload the page after corrections
when location is about to be deleted/saved after editing - alert window to be shown 

 2. clicking on location card -> new blade opens with grid of houses in this specific location (same presentation -> card 250 by 250 where images of this house in top of the card and description of the house like distance to the sea, pets allowance, parking etc so as the bank info) check the parking toggle functionality as it throws error when added
 edit button for houses (to be able to change image/video of the house, description and other house card data) also add house button which would open a module where info of the house will be added o populate the card(distance to the sea, owner list to chose owner house from and other necessary data like parking as now when parking tapped to save and describe it throws error(check the data flow and matching our marina_croatia.sql structure). owner isnt visible to any other user but super admin) . 
 when house is about to be deleted/saved after editing  - alert window to be shown 

 3. clicking on the house card inside the house grid blade user directed to the new blade with new grid of all units in this house (same style as a house grid where images/video of the unit is top and below the image is a discription of the unit like number of guest this unit can take number of bedrooms and bathrooms and what amenities this unit has)
 same flow as for the house card so admin could add/delete/update the unit info PLUS ONLY ADMIN WOULD HAVE A BUTTON bookings which would open the modal/calendar to see the available dates for this specific unit where bookings done by owner of the house this unit belongs to cant be corrected by anyone but by the owner of the house and way versa - admins bookings cant be corrected by owners of the house owners and admins bookings colors in calendar should be different for user friendly management i guess
  when suite is about to be deleted/saved after editing  - alert window to be shown 
 4. clicking on owners link -> owners card to be displayed with theit info including password (not visible by default) and visible by request where admin can update it to new one and send it to owner. also bank requesition must be there so as the view properties would lead to their houses grid
  owner card would be able to be edited by admin - update/delete image/photo of the owner
when owner is about to be deleted/saved after editing  - alert window to be shown 
 5. logout must lead to welcome blade not to login

 6. check setup_database file as i dont understand why do we need it for if we have our env file with all necessary credentials in it