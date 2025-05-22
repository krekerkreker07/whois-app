#/usr/bin/bash

docker run -d --rm --name whois-app -p 8080:80 -v "$PWD":/var/www/html -v "$PWD/laravel.conf":/etc/apache2/sites-available/000-default.conf whois-app:latest
