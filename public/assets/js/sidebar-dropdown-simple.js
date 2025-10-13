// Simple Sidebar Dropdown - Version 2
(function() {
    'use strict';
    
    console.log('Simple Sidebar Dropdown Loading...');
    
    function initDropdowns() {
        // پیدا کردن تمام dropdown items
        var dropdownItems = document.querySelectorAll('.sidebar-menu__item.has-dropdown');
        console.log('Found ' + dropdownItems.length + ' dropdown items');
        
        // برای هر dropdown item
        dropdownItems.forEach(function(item) {
            var link = item.querySelector('.sidebar-menu__link');
            var submenu = item.querySelector('.sidebar-submenu');
            
            if (link && submenu) {
                console.log('Setting up dropdown for:', item.querySelector('.text').textContent);
                
                // حذف event listener قبلی
                var newLink = link.cloneNode(true);
                link.parentNode.replaceChild(newLink, link);
                
                // اضافه کردن event listener جدید
                newLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    console.log('Dropdown clicked!');
                    
                    // تشخیص وضعیت فعلی
                    var isOpen = item.classList.contains('show');
                    
                    // بستن همه dropdownها
                    dropdownItems.forEach(function(otherItem) {
                        otherItem.classList.remove('show', 'active');
                        var otherSubmenu = otherItem.querySelector('.sidebar-submenu');
                        if (otherSubmenu) {
                            otherSubmenu.style.display = 'none';
                        }
                    });
                    
                    // اگر بسته بود، باز کن
                    if (!isOpen) {
                        item.classList.add('show', 'active');
                        submenu.style.display = 'block';
                        console.log('Opened dropdown');
                    } else {
                        console.log('Closed dropdown');
                    }
                });
            }
        });
    }
    
    // اجرا بعد از load شدن DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDropdowns);
    } else {
        initDropdowns();
    }
    
    // اجرا بعد از load شدن window (برای اطمینان)
    window.addEventListener('load', function() {
        setTimeout(initDropdowns, 500);
    });
    
    console.log('Simple Sidebar Dropdown Loaded');
})();