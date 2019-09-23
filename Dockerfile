FROM php:7-apache
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN cp /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled
COPY apache2.conf /etc/apache2
RUN service apache2 restart
RUN unzip davsoft_msqold.sql.zip