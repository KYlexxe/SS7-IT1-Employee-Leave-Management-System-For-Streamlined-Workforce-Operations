#!/bin/bash

# Set the document root dynamically
DOC_ROOT="/workspaces/${REPO_NAME}/htdocs"

# Create the document root directory and set permissions
mkdir -p $DOC_ROOT && chown -R www-data:www-data $DOC_ROOT && chmod -R 755 $DOC_ROOT

# Configure Apache to use the dynamic document root and enable directory indexing
sed -i "s|/var/www/html|$DOC_ROOT|g" /etc/apache2/sites-available/000-default.conf
echo "<Directory $DOC_ROOT>
    Options Indexes FollowSymLinks
    AllowOverride None
    Require all granted
</Directory>" >> /etc/apache2/apache2.conf

# Configure Apache to serve phpMyAdmin from /phpmyadmin
echo "Alias /phpmyadmin /usr/share/phpmyadmin" > /etc/apache2/conf-available/phpmyadmin.conf
a2enconf phpmyadmin

# Start Apache
apachectl -D FOREGROUND &

# Configure MySQL with specified username, password, and database name
service mysql start
mysql -e "CREATE DATABASE mariadb;"
mysql -e "CREATE USER 'mariadb'@'localhost' IDENTIFIED BY 'mariadb';"
mysql -e "GRANT ALL PRIVILEGES ON mariadb.* TO 'mariadb'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Keep the script running to prevent the container from exiting
tail -f /dev/null