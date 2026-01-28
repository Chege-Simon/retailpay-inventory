## RetailPay Inventory
## System Requirements

- PHP >= 8.2
- Composer
- Node.js & NPM
- SQLite or MySQL >= 8.0 or MariaDB >= 10.3
- Apache/Nginx web server

## Installation

The application will be available at `http://localhost:8000`

## Default Login Credentials

After seeding, you can login with default accounts on the login page
## Quick Start Guide

### For Development
```bash
# Clone repository
git clone https://github.com/Chege-Simon/retailpay-inventory.git
cd kk-wholesalers

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env file
# Then run migrations and seeders
php artisan migrate
php artisan db:seed

# Create storage link
php artisan storage:link

# Start development
npm run dev
php artisan serve

## Database Structure

### Main Tables

- **users** - System users with roles
- **branches** - Business branches
- **stores** - Individual stores under branches
- **products** - Product catalog
- **inventory** - Store-wise product stock
- **sales** - Sales transactions
- **sale_items** - Individual items in sales
- **transfers** - Inter-store transfer requests
- **stock_movements** - Inventory change history

## User Roles & Permissions

### Administrator
- Full system access
- Manage all branches and stores
- Approve and assign transfer sources
- View all reports and analytics
- Manage users and permissions

### Branch Manager
- Manage stores within their branch
- Forward transfer requests to admin
- View branch-level reports
- Manage branch inventory

### Store Manager
- Manage their assigned store
- Process sales
- Request restocks
- Acknowledge shipments/receipts
- View store reports

## Transfer Workflow

The transfer system follows a multi-stage approval process:

1. **Requested** - Store Manager submits restock request
2. **Pending Admin Approval** - Branch Manager forwards to Administrator
3. **Approved** - Administrator assigns source store
4. **In Transit** - Source store acknowledges shipment
5. **Completed** - Destination store acknowledges receipt

