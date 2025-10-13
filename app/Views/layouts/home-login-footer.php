<?php
$additional_js = $additional_js ?? [];
$baseAssets = UtilityHelper::baseUrl('public/assets');
?>
    <script src="<?= htmlspecialchars($baseAssets . '/js/jquery-3.7.1.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($baseAssets . '/js/boostrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($baseAssets . '/js/phosphor-icon.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($baseAssets . '/js/file-upload.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($baseAssets . '/js/plyr.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($baseAssets . '/js/full-calendar.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($baseAssets . '/js/jquery-ui.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($baseAssets . '/js/editor-quill.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($baseAssets . '/js/apexcharts.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($baseAssets . '/js/calendar.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($baseAssets . '/js/jquery-jvectormap-2.0.5.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($baseAssets . '/js/jquery-jvectormap-world-mill-en.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($baseAssets . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?= htmlspecialchars($baseAssets . '/js/persian-font-enforcer.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
    <?php foreach ($additional_js as $jsFile): ?>
        <script src="<?= htmlspecialchars($jsFile, ENT_QUOTES, 'UTF-8'); ?>"></script>
    <?php endforeach; ?>
</body>
</html>
