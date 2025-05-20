<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

//  fix absent of header Authorization in Apache
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $_SERVER['AUTHORIZATION'] = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $headers['Authorization'];
        $_SERVER['AUTHORIZATION'] = $headers['Authorization'];
    }
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
