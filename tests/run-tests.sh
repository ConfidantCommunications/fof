#!/bin/sh
php `pwd`/../phpunit.phar -c configuration.xml "$@" .
