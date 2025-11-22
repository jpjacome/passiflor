<?php
echo "<h2>Directory Listing</h2>";

function listDir($dir) {
    echo "<b>$dir</b><br><pre>";
    if (is_dir($dir)) {
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') continue;
            $path = $dir . '/' . $file;
            echo (is_dir($path) ? '[DIR] ' : '      ') . $file . "\n";
        }
    } else {
        echo "Not found or not a directory.\n";
    }
    echo "</pre>";
}

listDir(__DIR__); // public
listDir(dirname(__DIR__)); // project root
listDir(dirname(__DIR__) . '/vendor');
listDir(dirname(__DIR__) . '/bootstrap');
?>