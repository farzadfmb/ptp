// Font Awesome Loader and Checker
document.addEventListener('DOMContentLoaded', function() {
    // چک کردن لود شدن Font Awesome
    function checkFontAwesome() {
        // ایجاد element تست
        const testElement = document.createElement('i');
        testElement.className = 'fas fa-heart';
        testElement.style.position = 'absolute';
        testElement.style.left = '-9999px';
        testElement.style.fontSize = '12px';
        document.body.appendChild(testElement);
        
        // چک کردن width
        const width = window.getComputedStyle(testElement).width;
        const fontFamily = window.getComputedStyle(testElement).fontFamily;
        
        document.body.removeChild(testElement);
        
        // اگر Font Awesome لود شده باشد
        if (fontFamily.includes('Font Awesome') || width !== '0px') {
            document.body.classList.add('fontawesome-ready');
            console.log('✅ Font Awesome loaded successfully');
            return true;
        } else {
            document.body.classList.add('fa-fallback');
            console.log('❌ Font Awesome failed to load - using fallback');
            return false;
        }
    }
    
    // تست اولیه
    setTimeout(checkFontAwesome, 100);
    
    // تست مجدد بعد از 1 ثانیه
    setTimeout(function() {
        if (!document.body.classList.contains('fontawesome-ready')) {
            checkFontAwesome();
        }
    }, 1000);
    
    // Force apply Font Awesome styles
    function forceFontAwesome() {
        const icons = document.querySelectorAll('.fas, .far, .fab, [class^="fa-"], [class*=" fa-"]');
        icons.forEach(function(icon) {
            icon.style.fontFamily = '"Font Awesome 6 Free"';
            if (icon.classList.contains('far')) {
                icon.style.fontWeight = '400';
            } else if (icon.classList.contains('fab')) {
                icon.style.fontFamily = '"Font Awesome 6 Brands"';
                icon.style.fontWeight = '400';
            } else {
                icon.style.fontWeight = '900';
            }
            icon.style.fontStyle = 'normal';
            icon.style.fontVariant = 'normal';
            icon.style.textTransform = 'none';
            icon.style.lineHeight = '1';
            icon.style.webkitFontSmoothing = 'antialiased';
        });
    }
    
    // اعمال force styles
    setTimeout(forceFontAwesome, 200);
    
    // Debug info
    console.log('Font Awesome Debug Info:');
    console.log('- CDN Status: Checking...');
    
    // تست CDN
    const link = document.querySelector('link[href*="font-awesome"]');
    if (link) {
        link.addEventListener('load', function() {
            console.log('✅ Font Awesome CDN loaded');
            checkFontAwesome();
        });
        link.addEventListener('error', function() {
            console.log('❌ Font Awesome CDN failed');
            document.body.classList.add('fa-fallback');
        });
    }
});

// Helper برای debug
window.debugFontAwesome = function() {
    const icons = document.querySelectorAll('.fas, .far, .fab');
    console.log(`Found ${icons.length} Font Awesome icons`);
    
    icons.forEach(function(icon, index) {
        const styles = window.getComputedStyle(icon);
        console.log(`Icon ${index + 1}:`, {
            className: icon.className,
            fontFamily: styles.fontFamily,
            fontWeight: styles.fontWeight,
            content: styles.getPropertyValue('content')
        });
    });
};