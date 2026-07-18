<?php

$publicPath = realpath(dirname(__DIR__).DIRECTORY_SEPARATOR.'public');
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
$requestedFile = realpath($publicPath.str_replace('/', DIRECTORY_SEPARATOR, $uri));

if ($uri !== '/'
    && $requestedFile !== false
    && str_starts_with($requestedFile, $publicPath.DIRECTORY_SEPARATOR)
    && is_file($requestedFile)) {
    return false;
}

require $publicPath.DIRECTORY_SEPARATOR.'index.php';
