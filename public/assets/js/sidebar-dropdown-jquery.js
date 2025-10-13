// Sidebar Dropdown with jQuery - Final Version
$(document).ready(function() {
    console.log('jQuery Sidebar Dropdown Loading...');
    
    // حذف event handler های قبلی jQuery از main.js
    $('.has-dropdown').off('click');
    
    // تنظیم dropdown های جدید
    $('.sidebar-menu__item.has-dropdown').each(function() {
        var $item = $(this);
        var $link = $item.find('.sidebar-menu__link');
        var $submenu = $item.find('.sidebar-submenu');
        
        console.log('Setting up dropdown for:', $item.find('.text').text());
        
        $link.off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Dropdown clicked:', $item.find('.text').text());
            
            var isOpen = $item.hasClass('show') || $item.hasClass('active');
            
            // بستن همه dropdown ها
            $('.sidebar-menu__item.has-dropdown').removeClass('show active');
            $('.sidebar-submenu').slideUp(300);
            
            // باز کردن این dropdown اگر بسته بود
            if (!isOpen) {
                $item.addClass('show active');
                $submenu.slideDown(300);
                console.log('Opened dropdown');
            } else {
                console.log('Closed dropdown');
            }
        });
    });
    
    console.log('jQuery Sidebar Dropdown Ready');
});