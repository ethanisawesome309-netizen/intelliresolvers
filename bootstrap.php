<?php
$host = $_SERVER['HTTP_HOST'] ?? '';
$uri  = $_SERVER['REQUEST_URI'] ?? '/';

if ($host === 'intelliresolvers.com') {
    header("Location: https://www.intelliresolvers.com{$uri}", true, 301);
    exit;
}
