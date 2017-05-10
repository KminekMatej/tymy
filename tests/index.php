<?php
header("Content-Type: text/plain");
$outputing = FALSE;
$outputDir = "/var/www/tymy/tests/output";

$output = shell_exec("/var/www/tymy/vendor/bin/tester -p php -c /var/www/tymy/tests/php.ini /var/www/tymy/tests");
echo $output;

if(!$outputing)
    exit(0);
echo "********************************************* OUTPUT **********************************************************\n\n";

if ($handle = opendir($outputDir)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'actual') {
            $f = $outputDir . "/" . $file;
            echo "**** File [Actual]: $f ****\n\n";
            echo file_get_contents($f);
            echo "\n\n";
            echo "**** End of file: $f ****\n\n";
            $f = str_replace("actual", "expected", $f);
            echo "**** File [Expected]: $f ****\n\n";
            echo file_get_contents($f);
            echo "\n\n";
            echo "**** End of file: $f ****\n\n";
        }
    }
    closedir($handle);
}

echo "********************************************* END **********************************************************\n\n";