#!/bin/sh
date ;
php artisan clear-compiled
php artisan cache:clear
php artisan config:clear
php artisan event:clear
php artisan route:clear
php artisan view:clear

## git -C /www/html/test/uipps-api status  && \
## git status  && \
#git checkout -- .  && \
#git clean -fd  && \
#git pull
