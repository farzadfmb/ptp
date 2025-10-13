// Sidebar Dropdown Functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sidebar dropdown script loaded');
    
    // Wait a bit for other scripts to load
    setTimeout(function() {
        initializeSidebarDropdown();
    }, 100);
});

function initializeSidebarDropdown() {
    // تمام منوهای dropdown را پیدا کن
    const dropdownItems = document.querySelectorAll('.sidebar-menu__item.has-dropdown');
    console.log('Found dropdown items:', dropdownItems.length);
    
    dropdownItems.forEach(function(item, index) {
        const link = item.querySelector('.sidebar-menu__link');
        const submenu = item.querySelector('.sidebar-submenu');
        const arrow = item.querySelector('.dropdown-arrow');
        
        console.log('Item', index, '- Link:', !!link, 'Submenu:', !!submenu, 'Arrow:', !!arrow);
        
        if (link && submenu) {
            // کلیک روی منوی اصلی
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Dropdown clicked:', item.querySelector('.text').textContent);
                
                // چک کن آیا این منو باز است یا نه
                const isOpen = item.classList.contains('show') || item.classList.contains('active');
                
                // بقیه منوها را ببند
                dropdownItems.forEach(function(otherItem) {
                    if (otherItem !== item) {
                        closeDropdown(otherItem);
                    }
                });
                
                // این منو را toggle کن
                if (isOpen) {
                    closeDropdown(item);
                } else {
                    openDropdown(item);
                }
            });
        }
    });
    
    function openDropdown(item) {
        const submenu = item.querySelector('.sidebar-submenu');
        const arrow = item.querySelector('.dropdown-arrow');
        
        item.classList.add('show', 'active');
        
        if (submenu) {
            submenu.style.display = 'block';
            submenu.style.maxHeight = '0';
            submenu.style.opacity = '0';
            submenu.style.transform = 'translateY(-10px)';
            
            // Force reflow
            submenu.offsetHeight;
            
            setTimeout(() => {
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
                submenu.style.opacity = '1';
                submenu.style.transform = 'translateY(0)';
            }, 10);
        }
        
        if (arrow) {
            arrow.style.transform = 'rotate(180deg)';
        }
    }
    
    function closeDropdown(item) {
        const submenu = item.querySelector('.sidebar-submenu');
        const arrow = item.querySelector('.dropdown-arrow');
        
        item.classList.remove('show', 'active');
        
        if (submenu) {
            submenu.style.maxHeight = '0';
            submenu.style.opacity = '0';
            submenu.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                submenu.style.display = 'none';
            }, 300);
        }
        
        if (arrow) {
            arrow.style.transform = 'rotate(0deg)';
        }
    }
    
    // تشخیص صفحه فعال
    function setActiveMenu() {
        const currentPath = window.location.pathname;
        const submenuLinks = document.querySelectorAll('.sidebar-submenu__link');
        
        submenuLinks.forEach(function(link) {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href)) {
                // submenu link را active کن
                link.classList.add('active');
                
                // parent dropdown را باز کن
                const parentItem = link.closest('.sidebar-menu__item.has-dropdown');
                if (parentItem) {
                    openDropdown(parentItem);
                }
            }
        });
    }
    
    // اجرای تشخیص صفحه فعال
    setActiveMenu();
    
    // Mobile sidebar toggle
    const sidebarToggle = document.querySelector('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.querySelector('.side-overlay');
    const sidebarCloseBtn = document.querySelector('.sidebar-close-btn');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.add('show');
            if (sidebarOverlay) {
                sidebarOverlay.classList.add('show');
            }
        });
    }
    
    // بستن sidebar در موبایل
    if (sidebarCloseBtn && sidebar) {
        sidebarCloseBtn.addEventListener('click', function() {
            sidebar.classList.remove('show');
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('show');
            }
        });
    }
    
    // بستن sidebar با کلیک روی overlay
    if (sidebarOverlay && sidebar) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }
    
    console.log('Sidebar dropdown functionality initialized');
}