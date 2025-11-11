<?php

$files = glob(__DIR__ . '/*.php');

foreach ($files as $file) {
    if (basename($file) === 'update_namespaces.php') {
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Update namespace
    $content = preg_replace(
        '/namespace Database\\Seeders;/',
        'namespace Database\\Seeders\\Helpers;',
        $content
    );
    
    // Update class references in the same file (if any)
    $content = str_replace(
        'use Database\\Seeders',
        'use Database\\Seeders\\Helpers',
        $content
    );
    
    file_put_contents($file, $content);
    echo "Updated: " . basename($file) . "\n";
}

echo "All files have been updated.\n";
