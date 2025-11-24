<?php
/**
 * Session Helper - Tránh lỗi session_start() nhiều lần
 */

if (!function_exists('start_session_safe')) {
    function start_session_safe() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

