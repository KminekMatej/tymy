#!/bin/bash

RUN_PATH="$( cd "$(dirname "$0")" ; pwd -P )"
chmod -R 0777 $RUN_PATH/*
chmod -R 0777 $RUN_PATH/../temp/*
chmod -R 0777 $RUN_PATH/../log/*
TESTCMD="$RUN_PATH/../vendor/nette/tester/src/tester.php -p php -c $RUN_PATH/php.ini -j 4 --setup $RUN_PATH/setup_DEV.php $RUN_PATH/tapi/"
echo $TESTCMD
php $TESTCMD