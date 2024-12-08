# Use an official Ubuntu as a parent image
FROM ubuntu:20.04

# Install dependencies and PHP 8.2
RUN apt-get update && apt-get install -y \
    software-properties-common \
    sudo \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update && apt-get install -y \
    php8.2 \
    php8.2-mysql \
    php8.2-mysqli \
    php8.2-pdo \
    php8.2-pdo-mysql \
    apache2 \
    libapache2-mod-php8.2 \
    mariadb-server \
    mariadb-client \
    wget \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install phpMyAdmin
RUN wget https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.zip -O /tmp/phpmyadmin.zip \
    && unzip /tmp/phpmyadmin.zip -d /usr/share \
    && mv /usr/share/phpMyAdmin-*-all-languages /usr/share/phpmyadmin \
    && rm /tmp/phpmyadmin.zip

# Enable Apache mods
RUN a2enmod php8.2

# Set the ServerName directive globally to suppress the warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Expose port 80
EXPOSE 80

# Keep the container running by starting the startup script
CMD ["/bin/bash", "-c", "/workspaces/${localWorkspaceFolderBasename}/startup.sh"]