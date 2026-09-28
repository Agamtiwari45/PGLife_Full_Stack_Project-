<?php
require_once __DIR__ . '/session.php';
$_SESSION = [];
session_destroy();
header('Location: agam.php');
exit;
