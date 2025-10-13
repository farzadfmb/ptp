<?php
// Load helpers
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <!-- Title -->
    <title><?php echo $title ?? 'داشبورد ادمین'; ?></title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo UtilityHelper::baseUrl('public/assets/images/logo/favicon.png'); ?>">
    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Font Awesome Backup (local) -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/fontawesome-backup.css'); ?>">
    <!-- File upload -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/file-upload.css'); ?>">
    <!-- Plyr -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/plyr.css'); ?>">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Full calendar -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/full-calendar.css'); ?>">
    <!-- jQuery UI -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/jquery-ui.css'); ?>">
    <!-- Editor quill -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/editor-quill.css'); ?>">
    <!-- Apex charts -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/apexcharts.css'); ?>">
    <!-- Calendar -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/calendar.css'); ?>">
    <!-- JVector map -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/jquery-jvectormap-2.0.5.css'); ?>">
    <!-- Main css -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/main.css'); ?>">
    <!-- Persian Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33/dist/font-face.css">
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/persian-fonts-local.css'); ?>">
    <!-- RTL Custom -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/rtl-local.css'); ?>">
    <!-- Responsive and Icon Fixes -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/responsive-fixes.css'); ?>">
    <!-- Font Awesome RTL Fixes -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/fontawesome-rtl-fixes.css'); ?>">
    <!-- Font Awesome Complete Fix -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/fontawesome-complete-fix.css'); ?>">
    <!-- Font Awesome FORCE Override -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/fontawesome-force-override.css'); ?>">
    <!-- Font Awesome ULTIMATE Fix -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/fontawesome-ultimate-fix.css'); ?>">
    <!-- Sidebar Icons Fix -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/sidebar-icons-fix.css'); ?>">
    <!-- Unified RTL Theme -->
    <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl('public/assets/css/theme-rtl.css'); ?>">
    <!-- Additional CSS -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo UtilityHelper::baseUrl($css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Custom inline styles -->
    <?php if (isset($inline_styles) && trim($inline_styles) !== ''): ?>
        <style><?php echo $inline_styles; ?></style>
    <?php endif; ?>
</head> 
<body class="font-persian">
    
<!--==================== Preloader Start ====================-->
<div class="preloader">
    <div class="loader"></div>
</div>
<!--==================== Preloader End ====================-->

<!--==================== Sidebar Overlay ====================-->
<div class="side-overlay"></div>
<!--==================== Sidebar Overlay End ====================-->