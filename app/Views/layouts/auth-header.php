<?php
$title = $title ?? 'ورود سوپر ادمین';
$additional_css = $additional_css ?? [];
$baseAssets = UtilityHelper::baseUrl('public/assets');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="shortcut icon" href="<?= htmlspecialchars($baseAssets . '/images/logo/favicon.png', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseAssets . '/css/file-upload.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseAssets . '/css/plyr.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseAssets . '/css/full-calendar.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseAssets . '/css/jquery-ui.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseAssets . '/css/editor-quill.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseAssets . '/css/apexcharts.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseAssets . '/css/calendar.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseAssets . '/css/jquery-jvectormap-2.0.5.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseAssets . '/css/main.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33/dist/font-face.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseAssets . '/css/persian-fonts-local.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseAssets . '/css/theme-rtl.css', ENT_QUOTES, 'UTF-8'); ?>">
    <?php foreach ($additional_css as $cssFile): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($cssFile, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endforeach; ?>
</head>
<body class="font-persian">
    <div class="preloader">
        <div class="loader"></div>
    </div>
    <div class="side-overlay"></div>
