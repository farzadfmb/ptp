<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'داشبورد سازمان';
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$themeBase = UtilityHelper::baseUrl('public/themes/dashkote');
$assetBase = UtilityHelper::baseUrl('public/assets');
?>
<!doctype html>
<html lang="fa" dir="rtl" class="semi-dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="shortcut icon" href="<?= UtilityHelper::baseUrl('public/assets/images/logo/favicon.png'); ?>">

    <link rel="stylesheet" href="<?= $themeBase; ?>/css/pace.min.css">
    <script src="<?= $themeBase; ?>/js/pace.min.js" defer></script>

    <link rel="stylesheet" href="<?= $themeBase; ?>/plugins/perfect-scrollbar/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="<?= $themeBase; ?>/plugins/simplebar/css/simplebar.css">
    <link rel="stylesheet" href="<?= $themeBase; ?>/plugins/metismenu/css/metisMenu.min.css">

    <link rel="stylesheet" href="<?= $themeBase; ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $themeBase; ?>/css/bootstrap-extended.css">
    <link rel="stylesheet" href="<?= $themeBase; ?>/css/style.css">
    <link rel="stylesheet" href="<?= $themeBase; ?>/css/icons.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="preconnect" href="//fdn.fontcdn.ir">
    <link rel="preconnect" href="//v1.fontapi.ir">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    <link rel="stylesheet" href="https://v1.fontapi.ir/css/Yekan">
    <link rel="stylesheet" href="<?= $assetBase; ?>/css/persian-fonts-local.css">
    <link rel="stylesheet" href="<?= $assetBase; ?>/css/theme-rtl.css">

    <link rel="stylesheet" href="<?= $themeBase; ?>/css/dark-theme.css">
    <link rel="stylesheet" href="<?= $themeBase; ?>/css/semi-dark.css">
    <link rel="stylesheet" href="<?= $themeBase; ?>/css/header-colors.css">

    <?php foreach ($additional_css as $css): ?>
        <?php $cssHref = preg_match('/^https?:\/\//u', $css) ? $css : UtilityHelper::baseUrl($css); ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($cssHref, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endforeach; ?>

    <?php if ($inline_styles !== ''): ?>
        <style><?= $inline_styles; ?></style>
    <?php endif; ?>
</head>
<body class="font-persian">
    <div class="wrapper">
