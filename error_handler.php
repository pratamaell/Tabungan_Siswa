<?php
function handle_404() {
    http_response_code(404);
    require_once __DIR__ . '/../404.php';
    exit;
}

function check_page_exists($file) {
    if (!file_exists($file)) {
        handle_404();
    }
    return true;
}

// Tambahkan function untuk handle error lainnya
function handle_error($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    switch ($errno) {
        case E_USER_ERROR:
            http_response_code(500);
            require_once __DIR__ . '/../500.php';
            exit(1);
            break;

        case E_USER_WARNING:
        case E_USER_NOTICE:
            error_log("PHP Error [$errno] $errstr in $errfile on line $errline");
            break;
    }
    return true;
}

// Set error handler
set_error_handler("handle_error");