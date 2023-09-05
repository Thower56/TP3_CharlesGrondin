# Pour image de php
FROM php:fpm-alpine
# Utilise une petite image

RUN apk update; \
    apk upgrade;
# Met a jour le serveur

# Install mysqli
RUN docker-php-ext-install mysqli

