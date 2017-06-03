<?php
header("Content-Type: text/plain");
$output = shell_exec("/var/www/tymy/vendor/bin/tester -p php -c /var/www/tymy/tests/php.ini --setup /var/www/tymy/tests/setup_DEV.php /var/www/tymy/tests");
echo $output;
