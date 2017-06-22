<?php
header("Content-Type: text/plain");
runTest("setup_DEV");
runTest("setup_MONKEYS");

function runTest($phpSetup) {
    $output = shell_exec("/var/www/html/tymy/vendor/bin/tester -p php -c /var/www/html/tymy/tests/php.ini --setup /var/www/html/tymy/tests/$phpSetup.php /var/www/html/tymy/tests");
    echo $output;
}
