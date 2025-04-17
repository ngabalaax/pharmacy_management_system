# Hanan Pharmacy Management System - README

## Overview
The Hanan Pharmacy Management System is a comprehensive solution designed to streamline pharmacy operations, inventory management, sales tracking, and reporting. This system provides role-based access to different pharmacy staff members, ensuring secure and efficient management of pharmaceutical products.

## System Structure

### Database Structure
The system uses a MySQL database with the following key tables:

1. **Users**: Manages staff accounts with different roles (manager, pharmacist, store coordinator, cashier)
2. **Drugs**: Tracks all pharmaceutical products with inventory details
3. **Suppliers**: Stores supplier information
4. **Sales**: Records customer transactions
5. **Sale Items**: Details of products sold in each transaction
6. **Inventory Adjustments**: Tracks stock movements
7. **Expiry Alerts**: Monitors drug expiration dates
8. **Audit Log**: Records system activities for security and compliance

### File Structure
```
└── 📁htdocs
    └── 📁assets
        └── 📁css - All CSS stylesheets
        └── 📁images - System images and logos
        └── 📁js - JavaScript files
    └── 📁config - Database configuration
    └── 📁includes - Reusable components
    └── 📁modules - Core application modules
        └── 📁dashboard - Role-specific dashboards
        └── 📁inventory - Drug management
        └── 📁reports - Various system reports
        └── 📁sales - Sales processing
        └── 📁users - User management
    └── 📁website - Public-facing pages
```

## Key Features

### For Executive/CEO Understanding:
1. **Inventory Management**:
   - Real-time tracking of drug stock levels
   - Automatic low stock alerts
   - Expiration date monitoring

2. **Sales Processing**:
   - Efficient point-of-sale system
   - Multiple payment methods support
   - Sales history tracking

3. **Reporting**:
   - Sales performance reports
   - Inventory status reports
   - Expiry risk reports

4. **Security & Compliance**:
   - Role-based access control
   - Complete audit trail of all system activities
   - Data backup and recovery capabilities

5. **Operational Efficiency**:
   - Automated reorder level notifications
   - Streamlined workflow for different staff roles
   - Customer information tracking

### For Developers:

#### Technical Specifications
- **Backend**: PHP with MySQL database
- **Frontend**: HTML, CSS, JavaScript
- **Security**: Password hashing, input validation, CSRF protection
- **Database**: MySQL with proper indexing and constraints

#### Setup Instructions
1. Clone the repository to your web server's htdocs directory
2. Import the provided SQL file (`database.sql`) to create the database structure
3. Configure database connection in `config/database.php`
4. Access the system through your web browser

#### Key Components
1. **Authentication System** (`auth.php`):
   - Handles user login/logout
   - Manages session security
   - Implements role-based access control

2. **Inventory Management**:
   - `register_drug.php` - Add new drugs to inventory
   - `view_drugs.php` - Browse and search inventory
   - `check_expiry.php` - Monitor expiration dates

3. **Sales Processing**:
   - `process_sale.php` - Handle sales transactions
   - `sales_history.php` - View past sales

4. **Reporting**:
   - `sales_report.php` - Generate sales analytics
   - `expiry_report.php` - View expiring products
   - Inventory reports under `reports/inventory/`

#### Development Guidelines
1. Always use prepared statements for database queries
2. Follow the existing coding style and structure
3. Add appropriate comments for new code
4. Update the audit log for significant actions
5. Test changes thoroughly before deployment

## User Roles and Permissions

| Role              | Access Level                                                                 |
|-------------------|-----------------------------------------------------------------------------|
| Manager           | Full system access including reports and user management                    |
| Pharmacist        | Drug management, sales processing, basic reports                            |
| Store Coordinator | Inventory management, supplier information, stock adjustments               |
| Cashier           | Sales processing, customer information, limited inventory viewing           |

## Business Benefits

1. **Improved Inventory Control**: Reduce overstocking and stockouts with automated alerts
2. **Enhanced Regulatory Compliance**: Complete tracking of drug batches and expiration dates
3. **Increased Operational Efficiency**: Streamlined workflows for different staff roles
4. **Better Decision Making**: Comprehensive reporting for business insights
5. **Reduced Losses**: Minimize expired product write-offs with proactive alerts
6. **Customer Satisfaction**: Faster checkout process and better service

## Support and Maintenance
For technical support or system enhancements, please contact the development team. Regular backups and updates are recommended to ensure system security and performance.

---
