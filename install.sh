#!/bin/bash
composer install --no-dev -o
box build -v
sudo cp writeme.phar /usr/local/bin/writeme
