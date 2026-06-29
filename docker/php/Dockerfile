FROM php:8.3-fpm

# UID e GID do usuário
ARG UID=1000
ARG GID=1000

# Instala dependências do sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev

# Configura a extensão GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Instala extensões PHP
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    zip \
    gd \
    intl

# Instala Redis via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Limpa cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuração do usuário Non-Root para produção/local
RUN groupadd -g ${GID} laravel && \
    useradd -u ${UID} -g laravel -m -s /bin/bash laravel

# Define a pasta da aplicação
WORKDIR /var/www/html

# Ajusta permissões
RUN chown -R laravel:laravel /var/www/html

# Executa o container como usuário não-root
USER laravel

# Porta utilizada pelo PHP-FPM
EXPOSE 9000

# Inicia o PHP-FPM
CMD ["php-fpm"]