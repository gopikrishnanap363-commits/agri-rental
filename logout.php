<?php
/**
 * Agricultural Equipment Rental Management Portal
 * User Logout Script
 */

require_once __DIR__ . '/includes/auth.php';

logoutUser();
session_start();
setFlash('info', 'You have been logged out safely.');
header('Location: ' . BASE_URL . '/login.php');
exit;
