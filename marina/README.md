# Marina Croatia - Admin & Owner Management System

A comprehensive property management system for Croatian accommodations, featuring separate admin and owner workflows with booking management.

## Features

### Admin Workflow (Full System Control)
- **User Management**: Create and manage owner accounts
- **Location Management**: Add/edit locations (Brela, Split, Dubrovnik, etc.)
- **Property Management**: Create houses and assign to owners
- **Suite Management**: Add suites to houses with full details
- **Booking Management**: Create admin bookings and view all bookings
- **Content Management**: Edit main site content
- **System Administration**: Full control over all aspects

### Owner Workflow (Limited to Own Properties)
- **Property Viewing**: View assigned houses and suites (read-only)
- **Booking Management**: Full control over bookings for their properties
- **Calendar Management**: Interactive calendar for each suite
- **Booking Creation**: Create new bookings for their suites
- **Profile Management**: Update personal information

## Installation

1. **Prerequisites**
   - PHP 8.2+
   - MySQL 5.7+
   - Composer
   - Node.js & NPM

2. **Setup Database**
   - Create a MySQL database named `marina_croatia`
   - Import the provided `marina_croatia.sql` file
   - Update `.env` with your database credentials

3. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

4. **Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Setup**
   ```bash
   php setup_database.php
   ```

6. **Run the Application**
   ```bash
   php artisan serve
   ```

## Default Login Credentials

### Admin Access
- **Email**: admin@marinacroatia.com
- **Password**: password

### Owner Access
- **Email**: owner@marinacroatia.com  
- **Password**: password

## System Architecture

### Database Schema
- **Users**: Admin and owner accounts with role-based access
- **Locations**: Geographic locations (Split, Brela, etc.)
- **Houses**: Properties assigned to owners
- **Suites**: Individual rental units within houses
- **Bookings**: Booking records with source tracking (admin vs owner)
- **Booking Dates**: Individual date entries for conflict prevention

### User Roles & Permissions

#### Admin Permissions
- ✅ Create/manage owner accounts
- ✅ Create/edit locations, houses, and suites
- ✅ View all bookings (admin and owner created)
- ✅ Create admin bookings
- ✅ Edit site content
- ✅ Full system access

#### Owner Permissions
- ✅ View assigned properties (read-only)
- ✅ Create/edit/cancel own bookings
- ✅ View calendar for their suites
- ✅ Update personal profile
- ❌ Cannot edit property information
- ❌ Cannot access other owners' properties
- ❌ Cannot modify admin bookings

### Key Features

1. **Booking Conflict Prevention**
   - System prevents double-booking of dates
   - Clear visual distinction between admin and owner bookings
   - Automatic booking date generation via database triggers

2. **Role-Based Access Control**
   - Middleware ensures users only access authorized areas
   - Database-level security with owner_id filtering
   - Clear separation of admin vs owner functionality

3. **Interactive Calendar**
   - Monthly view with booking status indicators
   - Click-to-book functionality for available dates
   - Visual differentiation of booking sources

4. **Responsive Design**
   - Mobile-friendly interface
   - Based on the provided reference layout
   - Clean, professional appearance

## Quick Start

1. Clone/download the project
2. Create MySQL database: `marina_croatia`
3. Run: `composer install`
4. Configure `.env` with your database settings
5. Run: `php setup_database.php`
6. Run: `php artisan serve`
7. Visit: `http://localhost:8000`

**Login as Admin**: admin@marinacroatia.com / password
**Login as Owner**: owner@marinacroatia.com / password
