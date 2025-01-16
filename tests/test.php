<?php
$testRoot = '/home/f4499536/public_html/comp1230/assignments/project';
// Include the PHPUnit autoloader
require $testRoot.'/../assignment2/vendor/autoload.php';

// Use the necessary PHPUnit class
use PHPUnit\TextUI\Command;

// Manually constructing the command line arguments
$command = new Command;
$outputFilePath = $testRoot.'/tests/output.html';

try {
    if (file_exists($outputFilePath)) {
        unlink($outputFilePath);
    }
    // Emulating command line execution
    ob_start();  // Start output buffering
    $command->run([
        'phpunit',   // Dummy entry, could be any string.
        '-c', $testRoot.'/tests/phpunit.xml',
        '--testdox-html', $outputFilePath
    ], false);
    ob_end_clean();  // Discard the buffered output
    
    // Check if the output file was created and display its contents
    if (file_exists($outputFilePath)) {
        $output = file_get_contents($outputFilePath);
        echo $output;
        // Optionally delete the file after displaying it
        // unlink($outputFilePath);
    }
} catch (Exception $e) {
    echo "Error running PHPUnit tests: " . $e->getMessage();
}
