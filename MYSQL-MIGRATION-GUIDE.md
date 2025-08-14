# MySQL Migration Guide

This guide will help you convert your Digital Sign Management System from SQLite to MySQL.

## Prerequisites

- MySQL server installed and running
- PHP with MySQL PDO extension enabled
- Access to create databases and users in MySQL

## Step 1: Set Up MySQL Database

### Option A: Using MySQL Command Line

```sql
-- Connect to MySQL as root
mysql -u root -p

-- Create database
CREATE DATABASE digital_sign CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (replace with your preferred username/password)
CREATE USER 'signage_user'@'localhost' IDENTIFIED BY 'your_secure_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON digital_sign.* TO 'signage_user'@'localhost';
FLUSH PRIVILEGES;

-- Exit MySQL
EXIT;
```

### Option B: Using phpMyAdmin or Similar Tool

1. Create a new database named `digital_sign`
2. Set charset to `utf8mb4` and collation to `utf8mb4_unicode_ci`
3. Create a user with full privileges on this database

## Step 2: Update Configuration

1. Copy `config-mysql-template.php` to `includes/config.php`
2. Update the database credentials:

```php
define('DB_HOST', 'localhost');           // Your MySQL host
define('DB_NAME', 'digital_sign');       // Your database name
define('DB_USER', 'signage_user');       // Your MySQL username
define('DB_PASS', 'your_secure_password'); // Your MySQL password
```

## Step 3: Initialize MySQL Database

Run the MySQL setup script:

```
http://yoursite.com/path/to/setup-mysql.php
```

This will:
- Create all necessary tables
- Set up proper indexes for performance
- Create the default admin user (admin/admin123)

## Step 4: Migrate Existing Data (Optional)

If you have existing data in SQLite that you want to transfer:

```
http://yoursite.com/path/to/migrate-to-mysql.php
```

This will:
- Read all data from your existing SQLite database
- Transfer it to the new MySQL database
- Verify the migration was successful

## Step 5: Test the System

1. Visit the admin panel: `http://yoursite.com/path/to/admin/`
2. Login with: admin/admin123
3. Verify all functionality works
4. Visit the display: `http://yoursite.com/path/to/display/`

## Key Differences: SQLite vs MySQL

### Performance Improvements
- **Concurrent Access**: MySQL handles multiple simultaneous users better
- **Indexing**: Better query optimization with proper indexes
- **Caching**: MySQL has advanced caching mechanisms
- **Scalability**: Can handle larger datasets more efficiently

### New Features Available
- **Full-text Search**: Better search capabilities for announcements/events
- **Replication**: Can set up master/slave configurations
- **Backup Tools**: More robust backup and recovery options
- **User Management**: Granular permission control

### Schema Changes
- `INTEGER` → `INT` with proper AUTO_INCREMENT
- `TEXT` → `VARCHAR(n)` or `TEXT` based on content length
- `DATETIME` → `TIMESTAMP` with automatic updates
- Added proper indexes for better performance
- UTF8MB4 charset for full Unicode support (including emojis)

## Troubleshooting

### Connection Issues
- Verify MySQL server is running
- Check database credentials in config.php
- Ensure PHP has MySQL PDO extension enabled
- Check firewall settings if using remote MySQL

### Permission Issues
- Ensure MySQL user has CREATE, INSERT, UPDATE, DELETE, SELECT privileges
- Check file permissions on upload directories (775)

### Migration Issues
- Verify SQLite database exists and is readable
- Check that MySQL tables are created before migration
- Review error logs for specific issues

## Performance Optimization

### Recommended MySQL Settings
```sql
-- For better performance with the digital sign system
SET GLOBAL innodb_buffer_pool_size = 128M;
SET GLOBAL query_cache_size = 32M;
SET GLOBAL query_cache_type = 1;
```

### Regular Maintenance
```sql
-- Optimize tables monthly
OPTIMIZE TABLE presidents, board_members, announcements, events, users;

-- Analyze tables for better query planning
ANALYZE TABLE presidents, board_members, announcements, events, users;
```

## Security Considerations

1. **Change Default Password**: Update the admin password immediately
2. **Database User**: Use a dedicated MySQL user with minimal required privileges
3. **Connection Security**: Use SSL connections if MySQL is on a different server
4. **Regular Updates**: Keep MySQL server updated
5. **Backup Strategy**: Implement regular database backups

## Backup Commands

```bash
# Create backup
mysqldump -u signage_user -p digital_sign > backup_$(date +%Y%m%d).sql

# Restore backup
mysql -u signage_user -p digital_sign < backup_20240729.sql
```

## Support

If you encounter issues during migration:

1. Check the error logs in your web server
2. Verify MySQL error logs
3. Test database connection independently
4. Ensure all file permissions are correct

The system should now be running on MySQL with improved performance and scalability!
