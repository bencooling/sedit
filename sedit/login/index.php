<?php
include_once('../config.php');

session_start();
$_SESSION['authenticated'] = true;

header('location: ' . PUBLIC_SITE_URL);
exit;