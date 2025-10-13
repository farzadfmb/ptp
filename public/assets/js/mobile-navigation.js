// Mobile Navigation and Responsive Functionality

document.addEventListener('DOMContentLoaded', function() {
    
    // Mobile menu toggle functionality
    const toggleBtn = document.querySelector('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    const sideOverlay = document.querySelector('.side-overlay');
    const sidebarCloseBtn = document.querySelector('.sidebar-close-btn');
    
    // Ensure RTL body direction
    document.documentElement.setAttribute('dir', 'rtl');
    document.body.setAttribute('dir', 'rtl');
    
    // Toggle sidebar on mobile
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('show');
            sideOverlay.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
            
            // Update aria-expanded attribute
            const isExpanded = sidebar.classList.contains('show');
            toggleBtn.setAttribute('aria-expanded', isExpanded);
            
            // Force sidebar positioning
            if (isExpanded) {
                sidebar.style.right = '0';
                sidebar.style.left = 'auto';
            }
        });
    }
    
    // Close sidebar when clicking overlay
    if (sideOverlay) {
        sideOverlay.addEventListener('click', function() {
            closeSidebar();
        });
    }
    
    // Close sidebar with close button
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', function() {
            closeSidebar();
        });
    }
    
    // Close sidebar function
    function closeSidebar() {
        sidebar.classList.remove('show');
        sideOverlay.classList.remove('show');
        document.body.classList.remove('sidebar-open');
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', 'false');
        }
    }
    
    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('show')) {
            closeSidebar();
        }
    });
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Close sidebar on larger screens
            if (window.innerWidth >= 1200) {
                closeSidebar();
            }
            
            // Recalculate chart sizes if they exist
            if (typeof ApexCharts !== 'undefined') {
                setTimeout(function() {
                    window.dispatchEvent(new Event('resize'));
                }, 100);
            }
        }, 250);
    });
    
    // Improve dropdown positioning on mobile
    const dropdowns = document.querySelectorAll('.dropdown-menu');
    dropdowns.forEach(function(dropdown) {
        const dropdownToggle = dropdown.previousElementSibling;
        
        if (dropdownToggle) {
            dropdownToggle.addEventListener('click', function() {
                setTimeout(function() {
                    // Check if dropdown is going off-screen
                    const rect = dropdown.getBoundingClientRect();
                    const viewportWidth = window.innerWidth;
                    
                    if (rect.right > viewportWidth) {
                        dropdown.style.left = 'auto';
                        dropdown.style.right = '0';
                    }
                    
                    if (rect.left < 0) {
                        dropdown.style.right = 'auto';
                        dropdown.style.left = '0';
                    }
                }, 10);
            });
        }
    });
    
    // Fix table responsiveness
    const tables = document.querySelectorAll('table:not(.table-responsive table)');
    tables.forEach(function(table) {
        if (!table.closest('.table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
    
    // Improve form validation on mobile
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(function(input) {
        // Prevent iOS zoom on focus
        if (window.innerWidth <= 768) {
            const currentSize = window.getComputedStyle(input).fontSize;
            if (parseFloat(currentSize) < 16) {
                input.style.fontSize = '16px';
            }
        }
        
        // Add proper labels for accessibility
        if (!input.getAttribute('aria-label') && !input.getAttribute('aria-labelledby')) {
            const placeholder = input.getAttribute('placeholder');
            if (placeholder) {
                input.setAttribute('aria-label', placeholder);
            }
        }
    });
    
    // Chart responsive handling
    function handleChartResize() {
        if (typeof ApexCharts !== 'undefined') {
            setTimeout(function() {
                const charts = document.querySelectorAll('[id*="chart"]');
                charts.forEach(function(chartElement) {
                    const chart = ApexCharts.getChartByID(chartElement.id);
                    if (chart) {
                        chart.resize();
                    }
                });
            }, 300);
        }
    }
    
    // Call chart resize on orientation change
    window.addEventListener('orientationchange', function() {
        setTimeout(handleChartResize, 500);
    });
    
    // Touch event improvements for mobile
    if ('ontouchstart' in window) {
        // Add touch class for CSS styling
        document.body.classList.add('touch-device');
        
        // Improve touch targets
        const clickElements = document.querySelectorAll('a, button, .btn, [onclick]');
        clickElements.forEach(function(element) {
            if (element.offsetHeight < 44) {
                element.style.minHeight = '44px';
                element.style.display = 'flex';
                element.style.alignItems = 'center';
                element.style.justifyContent = 'center';
            }
        });
    }
    
    // Icon font fallback check
    function checkIconFonts() {
        // Create a test element to check if Phosphor icons are loading
        const testIcon = document.createElement('i');
        testIcon.className = 'ph ph-test';
        testIcon.style.position = 'absolute';
        testIcon.style.visibility = 'hidden';
        testIcon.style.fontFamily = 'Phosphor';
        document.body.appendChild(testIcon);
        
        setTimeout(function() {
            const computedStyle = window.getComputedStyle(testIcon);
            const fontFamily = computedStyle.getPropertyValue('font-family');
            
            if (!fontFamily.includes('Phosphor')) {
                // Fallback to text if icons don't load
                console.warn('Phosphor icons not loading properly');
                document.body.classList.add('icon-fallback');
            }
            
            document.body.removeChild(testIcon);
        }, 1000);
    }
    
    // Check icon fonts after a delay
    setTimeout(checkIconFonts, 500);
});