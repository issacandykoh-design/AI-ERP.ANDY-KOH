#!/bin/bash
/www/server/mysql/bin/mysql -S /tmp/mysql.sock -u root -p'TempRootPass123' <<'MYSQL_EOF'
CREATE DATABASE IF NOT EXISTS `hub.craveva.com`;
CREATE USER IF NOT EXISTS 'hubcraveva'@'localhost' IDENTIFIED BY '986L$o_}?tg-yeH|';
GRANT ALL PRIVILEGES ON `hub.craveva.com`.* TO 'hubcraveva'@'localhost';
FLUSH PRIVILEGES;
MYSQL_EOF
