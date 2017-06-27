#!/bin/bash

if [ -f vendor/bin/mozart ]; then
	MOZART=vendor/bin/mozart
    echo "Using local mozart installation"
else
	MOZART=$(composer config home --global)/vendor/bin/mozart
    if ! [ -f $MOZART ]; then
        echo "No Mozart installation found! Please check out devDependencies or install mozart globally. Aborting"
        exit 1
	fi
    echo "Using global mozart installation"
fi

php -C $MOZART compose
