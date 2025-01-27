<?php
require_once __DIR__ . '/../config/path_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once INCLUDES_PATH . 'check_auth.php';
require_once INCLUDES_PATH . 'check_admin.php';
?> 