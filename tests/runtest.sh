#!/bin/bash

RUN_PATH="$( cd "$(dirname "$0")" ; pwd -P )"
chmod -R 0777 $RUN_PATH/*
chmod -R 0777 $RUN_PATH/../tmp/*
chmod -R 0777 $RUN_PATH/../log/*
php $RUN_PATH/../vendor/nette/tester/src/tester.php -p php -c $RUN_PATH/php.ini --setup $RUN_PATH/setup_XDEV.php $RUN_PATH/