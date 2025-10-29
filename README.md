# eClass - Training Academy CRM

A professional WordPress plugin for managing training academies with comprehensive student enrollment, course scheduling, instructor management, and integrated billing.

## Features

### 📊 Smart Dashboard
- **KPI Cards**: Quick overview of total students, active courses, total revenue, and team members
- **Growth Indicators**: Track monthly changes in students and revenue
- **Recent Activity**: View latest enrollments and payments at a glance

### 👥 Students Management
- Complete CRUD operations (Create, Read, Update, Delete)
- Advanced filtering by enrollment status and course
- Search functionality
- CSV import/export for bulk operations
- Track enrollment dates and student notes

### 📚 Courses Management
- Full course lifecycle management
- Support for both Online and Offline courses
- Dynamic location/meeting link fields based on course type
- Instructor assignment
- Capacity tracking
- Course status management (Upcoming, Ongoing, Completed)
- Pricing and scheduling information
- CSV import/export

### 👨‍🏫 Instructors & Team Management
- Manage all team members in one place
- Role-based categorization (Instructor, Admin, Support)
- Specialization tracking
- Contact information management
- CSV import/export

### 💰 Billing & Payments
- Invoice generation and management
- Multiple payment status tracking (Paid, Pending, Overdue)
- Payment method recording
- Transaction code tracking
- Due date management
- Automatic payment date recording
- CSV import/export

## Design Features

### 🎨 Modern UI
- Professional and clean interface
- Gradient header design
- Card-based layouts
- Smooth animations and transitions
- Responsive design for all devices

### 🌐 RTL Support
- Full support for Right-to-Left languages
- Perfect for Arabic and other RTL languages
- Automatic text direction adjustment

### 📱 Responsive
- Mobile-friendly design
- Tablet optimized
- Desktop enhanced

## Installation

1. Upload the `eclass` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Access eClass from the WordPress admin menu

## Usage

### Adding Students
1. Go to eClass > Students
2. Click "Add Student"
3. Fill in student information
4. Select course and enrollment status
5. Save

### Creating Courses
1. Go to eClass > Courses
2. Click "Add Course"
3. Enter course details
4. Select course type (Online/Offline)
5. For online courses, add meeting link
6. For offline courses, add room/location
7. Assign instructor and set capacity
8. Save

### Managing Billing
1. Go to eClass > Billing & Payments
2. Click "Add Invoice"
3. Select student and course
4. Enter amount and due date
5. Set payment status
6. If paid, add payment method and transaction code
7. Save

### CSV Import/Export
- Each module (Students, Courses, Instructors, Billing) supports CSV operations
- Use "Import CSV" to bulk add records
- Use "Export CSV" to download current data
- Follow the format guidelines shown in import modals

## Technical Details

### Database Tables
- `wp_eclass_students`: Student records
- `wp_eclass_courses`: Course information
- `wp_eclass_instructors`: Team member data
- `wp_eclass_billing`: Billing and payment records

### Requirements
- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

### File Structure
```
eclass/
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
├── includes/
│   ├── class-eclass-admin.php
│   ├── class-eclass-billing.php
│   ├── class-eclass-courses.php
│   ├── class-eclass-csv-handler.php
│   ├── class-eclass-dashboard.php
│   ├── class-eclass-database.php
│   ├── class-eclass-instructors.php
│   └── class-eclass-students.php
├── eclass.php
└── README.md
```

## Support

For support and inquiries, visit [ePlusWeb.com](https://eplusweb.com)

## Credits

Developed by **ePlusWeb**
Website: https://eplusweb.com

## License

GPL v2 or later

## Changelog

### Version 1.0.0
- Initial release
- Dashboard with KPIs
- Students management
- Courses management
- Instructors & Team management
- Billing & Payments
- CSV import/export
- RTL support
- Modern responsive UI
