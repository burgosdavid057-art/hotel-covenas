<?php
require_once __DIR__ . '/auth.php';
$_SESSION = [];
session_destroy();
header('Location: ' . base_url('admin/login.php'));
exit;
