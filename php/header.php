<!DOCTYPE html>
<html lang="en-GB">
  <head>
    <?php
      $app_path_prefix = $app_path_prefix ?? '';
      $asset_prefix = $app_path_prefix;
    ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-QN708QFJSD"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-QN708QFJSD');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    <meta http-equiv="Content-Type" content="text/html">    
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
    <meta name="keywords" content="football, predictions, game">
	  <title><?= $page_title ?> - Hendy's Hunches</title>
    <link href="<?= htmlspecialchars($asset_prefix . 'ico/favicon.ico', ENT_QUOTES) ?>" rel="icon">
    <!-- Vendor CSS Files -->
    <link href="<?= htmlspecialchars($asset_prefix . 'vendor/bootstrap/css/bootstrap.min.css', ENT_QUOTES) ?>" rel="stylesheet">
    <link href="<?= htmlspecialchars($asset_prefix . 'vendor/bootstrap-icons/bootstrap-icons.css', ENT_QUOTES) ?>" rel="stylesheet">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.8/r-3.0.2/datatables.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />     
    <!-- Custom CSS Files -->
    <link href="<?= htmlspecialchars($asset_prefix . 'css/styles.css', ENT_QUOTES) ?>" rel="stylesheet">
    <!-- Include PHP Config File -->
    <?php require_once __DIR__ . "/config.php" ?>
    <?php require_once __DIR__ . "/process.php" ?>
    <!--jQuery Files -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
</head>
<body>
<?php hh_render_dev_banner($app_logout_path ?? ($asset_prefix . 'php/logout.php')); ?>
