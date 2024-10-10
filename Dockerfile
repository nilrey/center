# Используем официальный образ PHP с поддержкой Apache
FROM php:8.2-apache

# Установка зависимостей для работы с PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pgsql pdo pdo_pgsql

# Копируем проект внутрь контейнера
COPY ./src /var/www/html/

# Настройка прав доступа
RUN chown -R www-data:www-data /var/www/html

# Включение модуля Apache mod_rewrite
RUN a2enmod rewrite
