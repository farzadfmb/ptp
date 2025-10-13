        <div class="dashboard-footer">
            <div class="flex-between flex-wrap gap-16">
                <p class="text-gray-300 text-13 fw-normal">&copy; کپی‌رایت ادمیت ۲۰۲۴، تمامی حقوق محفوظ است</p>
                <div class="flex-align flex-wrap gap-16">
                    <a href="#" class="text-gray-300 text-13 fw-normal hover-text-main-600 hover-text-decoration-underline">مجوز</a>
                    <a href="#" class="text-gray-300 text-13 fw-normal hover-text-main-600 hover-text-decoration-underline">قالب‌های بیشتر</a>
                    <a href="#" class="text-gray-300 text-13 fw-normal hover-text-main-600 hover-text-decoration-underline">مستندات</a>
                    <a href="#" class="text-gray-300 text-13 fw-normal hover-text-main-600 hover-text-decoration-underline">پشتیبانی</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Jquery js -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/jquery-3.7.1.min.js'); ?>"></script>
    <!-- Bootstrap Bundle Js -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/boostrap.bundle.min.js'); ?>"></script>
    <!-- Phosphor Js -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/phosphor-icon.js'); ?>"></script>
    <!-- File upload -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/file-upload.js'); ?>"></script>
    <!-- Plyr -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/plyr.js'); ?>"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/datatables-init.js'); ?>"></script>
    <!-- Full calendar -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/full-calendar.js'); ?>"></script>
    <!-- jQuery UI -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/jquery-ui.js'); ?>"></script>
    <!-- Editor quill -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/editor-quill.js'); ?>"></script>
    <!-- Apex charts -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/apexcharts.min.js'); ?>"></script>
    <!-- Persian Calendar -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/simple-persian-calendar.js'); ?>"></script>
    <!-- JVectormap -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/jquery-jvectormap-2.0.5.min.js'); ?>"></script>
    <!-- JVectormap world -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/jquery-jvectormap-world-mill-en.js'); ?>"></script>
    
    <!-- Main js -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/main.js'); ?>"></script>
    
    <!-- Mobile Navigation -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/mobile-navigation.js'); ?>"></script>
    
    <!-- Persian Font Enforcer -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/persian-font-enforcer.js'); ?>"></script>
    
    <!-- Font Awesome Loader -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/fontawesome-loader.js'); ?>"></script>
    
    <!-- Sidebar Dropdown -->
    <script src="<?php echo UtilityHelper::baseUrl('public/assets/js/sidebar-dropdown-jquery.js'); ?>"></script>

    <!-- Additional JS -->
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo UtilityHelper::baseUrl($js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Custom inline scripts -->
    <?php if (isset($inline_scripts)): ?>
        <script><?php echo $inline_scripts; ?></script>
    <?php endif; ?>
    
</body>
</html>