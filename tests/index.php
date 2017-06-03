<?php
header("Content-Type: text/plain");
//runTest("setup_DEV");
runTest("setup_MONKEYS");

function runTest($phpSetup) {
    $output = shell_exec("/var/www/tymy/vendor/bin/tester -p php -c /var/www/tymy/tests/php.ini --setup /var/www/tymy/tests/$phpSetup.php /var/www/tymy/tests");
    echo $output;
}
