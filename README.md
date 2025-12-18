# AgriShop Rwanda

A comprehensive web-based platform bridging the gap between Rwandan farmers and buyers, empowering farmers to list their produce, access market advisory services, and manage inventory while providing buyers with a seamless purchasing experience.

---

## Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Technologies Used](#technologies-used)
- [Setup Instructions](#setup-instructions)
- [API Documentation](#api-documentation)
- [Security Features](#security-features)
- [USSD Feature](#ussd-feature)
- [Project Deliverables](#project-deliverables)
- [Known Limitations](#known-limitations)
- [Production Deployment](#production-deployment)
- [Live Links](#live-links)

---

## Overview

AgriShop is designed to bridge the digital divide in Rwanda's agricultural sector by providing:

- **Bilingual Support**: Full English and Kinyarwanda content for local accessibility
- **Market Access**: Direct farmer-to-buyer marketplace with quality assurance
- **Advisory Services**: Real-time weather, market alerts, and farming guides
- **Inclusive Design**: Mobile-responsive interface with USSD simulation for low-tech environments

---

## Key Features

### 🛡️ User Management
- **User Roles**: Farmers and Buyers with role-based access control
- **Profile Management**: Update names, phone numbers, bio, address, and profile pictures
- **Secure Authentication**: Password hashing, secure sessions, and password recovery

### 🛒 Marketplace
- **Bilingual Listings**: Product names and details in English and Kinyarwanda
- **Visual Product Display**: High-quality images and category icons
- **Quality Badges**: Verified "Organic" and "Certified" labels for quality assurance
- **Smart Search & Filtering**: Advanced product discovery

### 📊 Shopping & Orders
- **Persistent Cart**: Cross-session shopping cart functionality
- **Order Tracking**: Complete order lifecycle from placement to completion
- **Delivery Management**: Detailed delivery instructions and status updates
- **Multi-Seller Support**: Automatic order splitting by seller

### 📈 Analytics Dashboard
- **Inventory Tracking**: Visual charts for farmer inventory management
- **Sales Analytics**: Performance insights and trends
- **Order Management**: Comprehensive order history and status tracking

### 🌍 Advisory Services
- **Live Weather Data**: Integrated Open-Meteo API for location-based weather
- **Interactive Maps**: Leaflet integration for geographical insights
- **Market Intelligence**: Real-time market alerts and pricing information
- **Farming Resources**: Guides and quality standards (bilingual)

### 📱 Mobile Accessibility
- **Responsive Design**: Fully responsive using Tailwind CSS
- **USSD Simulator**: Offline-first accessibility demonstration
- **Cross-Device Support**: Optimized for smartphones, tablets, and desktops

---

## Technologies Used

### Frontend
- **HTML5**: Semantic markup and modern web standards
- **Tailwind CSS**: Utility-first responsive design framework
- **JavaScript (ES6+)**: Interactive functionality and dynamic content
- **Chart.js**: Data visualization for analytics dashboard

### Backend
- **PHP 7.4+**: Server-side application logic
- **PDO**: Database abstraction with prepared statements
- **Session Management**: Secure authentication state handling

### Database
- **MySQL 5.7+** / **MariaDB**: Relational database management

### External APIs
- **Open-Meteo**: Weather data integration
- **Leaflet**: Interactive mapping and geolocation

---

## Setup Instructions

### Prerequisites
- XAMPP (Apache & MySQL) or equivalent LAMP/WAMP stack
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB

### 1. Environment Setup
1. Install and start XAMPP (Apache and MySQL services)
2. Clone or download this project to `xampp/htdocs/final project/`

### 2. Database Configuration

#### Step 1: Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `agrishop`

#### Step 2: Import Base Schema
1. In phpMyAdmin, select the `agrishop` database
2. Import `database.sql` from the project root

#### Step 3: Run Database Migrations
Execute migrations in the following order:

```
http://localhost/final project/dev/update_schema_order_status.php
```

This updates the order status ENUM with the complete lifecycle.

### 3. Create Admin User

Run the admin creation script:
```
http://localhost/final project/dev/create_admin_user.php
```

**Default Admin Credentials:**
- Email: `admin@agrishop.com`
- Password: `Admin@2025`

> ⚠️ **Security**: Delete `dev/create_admin_user.php` after creating the admin account

### 4. Launch Application

Access the application at:
- **Home Page**: `http://localhost/final project/index.html`
- **Admin Panel**: `http://localhost/final project/admin.html`

### 5. Development Tools

All debug and maintenance scripts are in the `dev/` directory:
- Database migration scripts
- Admin user creation
- Schema update utilities

> ⚠️ **Important**: Do not deploy the `dev/` directory to production

---

## API Documentation

All API endpoints use JSON for request/response payloads and support CORS for credential-based requests.

### Authentication & User Management

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `api/auth.php` | POST | User authentication (register, login, logout, forgot/reset password) |
| `api/user.php` | GET, PUT | User profile retrieval and updates |

### Product Management

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `api/products.php` | GET, POST, PUT, DELETE | Product CRUD operations and search |
| `api/reviews.php` | GET, POST | Product review and rating system |

### Shopping & Orders

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `api/orders.php` | GET, POST, PUT | Order management with status tracking |
| `api/wishlist.php` | GET, POST, DELETE | Wishlist operations |

### Advisory & Notifications

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `api/advisory.php` | GET, POST, PUT, DELETE | Advisory content management (bilingual) |
| `api/notifications.php` | GET, PUT | User notification management |

### Administration

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `api/admin.php` | GET, POST | Admin panel operations - **Requires admin role** |

### Order Lifecycle

Orders follow a strict status progression:

```
1. pending           → Order placed by buyer
2. confirmed         → Seller accepts order
3. shipped           → Order dispatched
4. delivered         → Order arrives at destination
5. receipt_confirmed → Buyer confirms receipt
6. completed         → Order finalized
7. cancelled         → Order cancelled (possible at any stage)
```

---

## Security Features

### Password Security
- **Hashing Algorithm**: `password_hash()` with `PASSWORD_DEFAULT` (bcrypt)
- **Verification**: Secure comparison using `password_verify()`
- **Password Reset**: Token-based recovery with time-limited reset links

### Session Management
- **Secure Cookies**: `httponly` and `samesite` flags enabled
- **Server-Side Validation**: Authentication state verified on every request
- **Role-Based Access Control (RBAC)**: Separate permissions for admin, farmer, and buyer roles

### Database Security
- **SQL Injection Prevention**: PDO prepared statements with parameter binding
- **Input Validation**: Server-side sanitization and validation for all user inputs
- **CORS Configuration**: Controlled cross-origin resource sharing with credentials

---

## USSD Feature

### Implementation Type: **Simulation/Demonstration**

The USSD feature is a **client-side JavaScript simulation** demonstrating accessibility for low-tech environments. It is **not** a live telecom integration.

### Purpose
- Demonstrate offline-first design philosophy
- Illustrate accessibility for users without smartphones
- Showcase AgriShop's commitment to inclusive technology

### User Flow

```
1. Navigate to ussd.html
2. USSD Simulator displays menu options
3. User inputs choice (1-3, 0 for back)
4. JavaScript updates display based on selection
5. Available features:
   - Market Prices (view average commodity prices)
   - Weather Info (simulated location-based weather)
   - Account Balance (simulated user data)
```

### Production USSD Integration

For real USSD deployment, you would need:
- Partnership with telecom provider (MTN, Airtel Rwanda, etc.)
- USSD gateway API integration
- Backend endpoint to process USSD requests/responses
- Session management for multi-step USSD flows

---

## Project Deliverables

- ✅ **Source Code**: Complete PHP/HTML/JavaScript codebase
- ✅ **Database Schema**: `database.sql` (base) + migration scripts in `dev/`
- ✅ **EER Diagram**: Detailed entity-relationship documentation in `EER_Diagram.md`
- ✅ **Framing Template**: Strategy document in `Framing_Template.md`
- ✅ **README**: Comprehensive setup and deployment guide

---

## Known Limitations

### CSRF Protection
- **Status**: ❌ Not implemented
- **Impact**: Forms and APIs do not use CSRF tokens
- **Mitigation for Production**: Implement synchronized token pattern or rely on SameSite cookies
- **Academic Context**: Acceptable for educational projects; required for production

### USSD Integration
- **Current**: Client-side simulation only
- **Production**: Requires telecom partnership and backend integration

### File Upload Validation
- **Current**: Basic validation (MIME type, file size)
- **Enhancement**: Add malware scanning and advanced content validation for production

---

## Production Deployment

### Pre-Deployment Checklist

Before deploying to a live server, complete these critical tasks:

#### Security Hardening
- [ ] Remove or password-protect the `dev/` directory
- [ ] Change all default passwords (especially admin account)
- [ ] Enable HTTPS and update cookie security settings
- [ ] Implement CSRF protection across all forms and APIs
- [ ] Configure restrictive CORS policies

#### Configuration
- [ ] Update database credentials in all PHP files
- [ ] Set `display_errors = Off` in PHP configuration
- [ ] Enable error logging to secure log files
- [ ] Configure rate limiting on API endpoints

#### File Security
- [ ] Set proper file permissions (644 for files, 755 for directories)
- [ ] Implement malware scanning for uploaded files
- [ ] Restrict uploads directory from executing PHP scripts

#### Monitoring & Backup
- [ ] Set up automated database backups
- [ ] Configure server monitoring and alerts
- [ ] Implement application logging

---

## Live Links

- **Public URL**: [INSERT DEPLOYMENT URL]
- **Video Demo**: [INSERT VIDEO DEMONSTRATION URL]

---

## Support & Contribution

For issues, questions, or contributions, please refer to the project documentation or contact the development team.

---

**License**: [Specify License]  
**Version**: 1.0.0  
**Last Updated**: December 2025