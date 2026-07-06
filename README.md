# QUARTZ Event Management System

## Overview
QUARTZ is a comprehensive web-based event management system designed to streamline event registration, attendance tracking via QR codes, evaluation completions, and rule-based certificate generation and dissemination.

The system supports multiple attendance configurations (one-time or AM/PM), static or dynamic participants, and complex rule-based requirements for granting certificates.

## Developer
Developed by: Google DeepMind Advanced Agentic Coding Team / Antigravity

## Tech Stack
This application is built with:
- **Backend:** Laravel 11.x, PHP 8.3
- **Frontend:** Vue.js 3, Inertia.js v3, TailwindCSS v4
- **Database:** SQLite (default) / MySQL / PostgreSQL
- **Key Packages:**
  - `spatie/laravel-permission` for robust Role-Based Access Control (RBAC)
  - `bacon/bacon-qr-code` for dynamic QR code generation
  - `barryvdh/laravel-dompdf` for automated certificate rendering

## Modules Implemented
- **User Management Module:** Create users and assign roles (Super Admin, Admin, Event Organizer, Participant).
- **Event Management Module:** Create and configure events, assign organizers, set registration and attendance rules.
- **Participant Management Module:** Upload participant lists or allow public registration.
- **QR Code Module:** Generating QR codes for participants for attendance tracking.
- **Attendance Management Module:** Log attendance using multiple scanning modes (One-time, AM/PM).
- **Evaluation Module:** Ensures participants complete required evaluations before certificates are released. Includes dynamic evaluation form builder.
- **Certificate Management Module:** Template creation, dynamic field mapping, and rule-based generation.
- **Reports Module:** Summaries for admins and organizers regarding attendance, evaluations, and certificates.

## Installation & Setup

1. **Clone the repository and install PHP dependencies:**
   ```bash
   composer install
   ```

2. **Install NPM dependencies:**
   ```bash
   npm install
   ```

3. **Environment Setup:**
   Copy the example environment file and generate an application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database & Migrations:**
   Run the database migrations and seeders to set up the default Roles and the Super Admin user:
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Run the Application:**
   Start the Laravel development server and Vite for frontend assets:
   ```bash
   npm run dev
   ```

## Default Credentials
After running the seeder, the following users are available (Password is `password` for all):
- **Super Admin:** `superadmin@example.com`
- **Admin:** `admin@example.com`
- **Event Organizer:** `organizer@example.com`
- **Participant:** `participant@example.com`
