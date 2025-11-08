<?php

/**
 * Quick check for PHP upload limits
 * Access: http://localhost:8000/check_php_upload_limits.php
 */

echo "<h2>PHP Upload Configuration</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Current Value</th><th>Recommended</th></tr>";

$settings = [
    'upload_max_filesize' => ['current' => ini_get('upload_max_filesize'), 'recommended' => '500M'],
    'post_max_size' => ['current' => ini_get('post_max_size'), 'recommended' => '500M'],
    'max_execution_time' => ['current' => ini_get('max_execution_time'), 'recommended' => '300'],
    'memory_limit' => ['current' => ini_get('memory_limit'), 'recommended' => '512M'],
    'max_file_uploads' => ['current' => ini_get('max_file_uploads'), 'recommended' => '20'],
];

foreach ($settings as $key => $value) {
    $status = '✅';
    if ($key === 'upload_max_filesize' || $key === 'post_max_size') {
        $currentBytes = return_bytes($value['current']);
        $recommendedBytes = return_bytes($value['recommended']);
        if ($currentBytes < $recommendedBytes) {
            $status = '❌';
        }
    }
    echo "<tr>";
    echo "<td><strong>{$key}</strong></td>";
    echo "<td>{$status} {$value['current']}</td>";
    echo "<td>{$value['recommended']}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>PHP Configuration File Location:</h3>";
echo "<pre>" . php_ini_loaded_file() . "</pre>";

echo "<h3>How to Fix:</h3>";
echo "<ol>";
echo "<li>Edit: <code>" . php_ini_loaded_file() . "</code></li>";
echo "<li>Update these values:</li>";
echo "<pre>";
echo "upload_max_filesize = 500M\n";
echo "post_max_size = 500M\n";
echo "max_execution_time = 300\n";
echo "memory_limit = 512M\n";
echo "</pre>";
echo "<li>Restart PHP server (if using built-in server, restart it)</li>";
echo "</ol>";

function return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $val = (int)$val;
    switch ($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}
