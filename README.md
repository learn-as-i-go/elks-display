# Digital Sign Management System

A PHP/MySQL-based digital signage solution for managing rotating content displays.

## Features
- Web-based content management interface
- Image upload for presidents and board members
- Announcement and event management
- Auto-refreshing display for Raspberry Pi devices
- MySQL database for improved performance and scalability
- Legacy SQLite support with migration tools

## Structure
- `/admin/` - Management interface
- `/display/` - Display application for Pi devices
- `/api/` - REST API endpoints
- `/uploads/` - Image storage
- `/includes/` - Core PHP classes and configuration

## Setup Options

### New MySQL Installation
1. Set up MySQL database and user
2. Copy `config-mysql-template.php` to `includes/config.php`
3. Update database credentials in `includes/config.php`
4. Run `setup-mysql.php` to initialize database
5. Set proper permissions on uploads/ directory (775)
6. Access `/admin/` to begin content management
7. Point Pi devices to `/display/`

### Migration from SQLite
1. Follow MySQL setup steps above
2. Run `migrate-to-mysql.php` to transfer existing data
3. Test functionality in admin panel

See `MYSQL-MIGRATION-GUIDE.md` for detailed instructions.

## Display Layout
- Main content area (left): Announcements, events, updates (10s rotation)
- Presidents section (top right): Historical photos 1919-present (5s rotation)
- Board/Officers section (bottom right): Current leadership (5s rotation)
