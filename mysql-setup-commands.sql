-- MySQL Setup Commands for Digital Sign System
-- Run these commands as MySQL root user

-- 1. Create the database
CREATE DATABASE IF NOT EXISTS digital_sign CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Create a dedicated user (replace 'your_password' with a secure password)
CREATE USER IF NOT EXISTS 'signage_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';

-- 3. Grant all privileges on the digital_sign database
GRANT ALL PRIVILEGES ON digital_sign.* TO 'signage_user'@'localhost';

-- 4. If you need the user to create databases (for setup script), grant additional privileges
-- GRANT CREATE ON *.* TO 'signage_user'@'localhost';

-- 5. Apply the changes
FLUSH PRIVILEGES;

-- 6. Verify the user was created
SELECT User, Host FROM mysql.user WHERE User = 'signage_user';

-- 7. Check the privileges granted
SHOW GRANTS FOR 'signage_user'@'localhost';

-- Alternative: If you want to use an existing MySQL user, just grant privileges:
-- GRANT ALL PRIVILEGES ON digital_sign.* TO 'your_existing_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Common troubleshooting commands:
-- 
-- To see all users:
-- SELECT User, Host FROM mysql.user;
--
-- To drop a user if you need to recreate:
-- DROP USER 'signage_user'@'localhost';
--
-- To change a user's password:
-- ALTER USER 'signage_user'@'localhost' IDENTIFIED BY 'new_password';
--
-- To see what databases exist:
-- SHOW DATABASES;
--
-- To see current user and privileges:
-- SELECT USER(), CURRENT_USER();
-- SHOW GRANTS;
