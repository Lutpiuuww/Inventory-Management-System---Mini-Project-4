<?php
$base_dir = str_replace('\\', '/', dirname(__DIR__));
echo "Base Dir: " . $base_dir . "\n";
echo "Doc Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
$base_url = str_replace($_SERVER['DOCUMENT_ROOT'], '', $base_dir);
echo "Base URL: " . $base_url . "\n";
