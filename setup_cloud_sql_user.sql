-- Run this on your Cloud SQL instance (e.g., via Cloud Shell or MySQL client)
CREATE DATABASE IF NOT EXISTS `hub.craveva.com`;
-- Note: Using '%' to allow connection from any authorized network (like your VM)
CREATE USER IF NOT EXISTS 'hubcraveva'@'%' IDENTIFIED BY '986L$o_}?tg-yeH|';
GRANT ALL PRIVILEGES ON `hub.craveva.com`.* TO 'hubcraveva'@'%';
FLUSH PRIVILEGES;
