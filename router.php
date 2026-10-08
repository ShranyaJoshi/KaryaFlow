<?php
// router.php - Static File, Auth & API Routing
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$root = __DIR__;
$publicDir = $root . '/public';

// Direct file routing (.css, .js, .png, .html)
$targetFile = $publicDir . $uri;
if ($uri !== '/' && file_exists($targetFile) && !is_dir($targetFile)) {
    $ext = pathinfo($targetFile, PATHINFO_EXTENSION);
    if ($ext === 'css') {
        header('Content-Type: text/css');
    } elseif ($ext === 'js') {
        header('Content-Type: application/javascript');
    }
    readfile($targetFile);
    exit;
}

// Named routes without .html extension
if ($uri === '/login') {
    readfile($publicDir . '/login.html');
    exit;
}
if ($uri === '/signup') {
    readfile($publicDir . '/signup.html');
    exit;
}
if ($uri === '/profile') {
    readfile($publicDir . '/profile.html');
    exit;
}

// API routing
if (strpos($uri, '/api/') === 0) {
    $apiFile = $root . $uri;
    if (file_exists($apiFile)) {
        require $apiFile;
        exit;
    }
}

// Dashboard fallback
if (file_exists($publicDir . '/index.html')) {
    readfile($publicDir . '/index.html');
    exit;
}