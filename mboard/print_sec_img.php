<?php
session_start();
define('IN_SCRIPT',true);

if (empty($_SESSION['secnum']) || strlen($_SESSION['secnum']) != 5 || preg_match('/\D/',$_SESSION['secnum'])) {
    die('Invalid or missing security number');
}

require('settings.php');
require('secimg.inc.php');
$sc=new PJ_SecurityImage($settings['filter_sum']);
$sc->printImage($_SESSION['secnum']);

exit();
?>
