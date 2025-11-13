# Use official PHP image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libicu-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (both MySQL and PostgreSQL for flexibility)
# Note: OpenSSL is built into PHP core and doesn't need to be installed separately
RUN docker-php-ext-install pdo_mysql mysqli pdo_pgsql pgsql mbstring exif pcntl bcmath gd intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy entrypoint script first (for better Docker layer caching)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy application files
COPY . /var/www/html

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create upload directories and set permissions
# Note: These will be recreated at runtime by docker-entrypoint.sh, but creating them here ensures they exist
RUN mkdir -p /var/www/html/public/uploads/profile \
    && mkdir -p /var/www/html/public/uploads/payment_proofs \
    && mkdir -p /var/www/html/public/uploads/payment_methods/qr_codes \
    && chown -R www-data:www-data /var/www/html/writable \
    && chown -R www-data:www-data /var/www/html/public/uploads \
    && chmod -R 775 /var/www/html/writable \
    && chmod -R 775 /var/www/html/public/uploads \
    && echo "✅ Upload directories created in Dockerfile"

# Configure Apache to use public directory as document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Expose port 80
EXPOSE 80

# Use entrypoint script that runs migrations on startup
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

