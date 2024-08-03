FROM php:8.3.2

WORKDIR /var/www/html

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt install --yes symfony-cli

RUN apt-get update && \
    apt install --yes libzip-dev unzip && \
    docker-php-ext-install zip

RUN apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

ADD https://raw.githubusercontent.com/vishnubob/wait-for-it/81b1373f17855a4dc21156cfe1694c31d7d1792e/wait-for-it.sh /usr/bin/wait-for-it
RUN chmod +x /usr/bin/wait-for-it

RUN pecl install redis
RUN docker-php-ext-enable redis

RUN pecl install pcov
RUN docker-php-ext-enable pcov

COPY ./php.ini /usr/local/etc/php/php.ini
COPY . .

CMD ["wait-for-it", "mariadb:3306", "--", "php", "-S", "0.0.0.0:80", "-t", "/var/www/html/public" ]