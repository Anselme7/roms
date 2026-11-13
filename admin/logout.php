<?php
/**
 * Déconnexion Administration
 */
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/auth.php';

unset($_SESSION['admin_user_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);
unset($_SESSION['admin_login']);

header('Location: ' . BASE_URL . '/admin/login.php');
exit;

