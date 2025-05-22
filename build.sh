#/usr/bin/bash

docker build --no-cache -t whois-app .
docker run --rm -v "$PWD":/var/www/html whois-app:latest composer install && cp .env.example .env
