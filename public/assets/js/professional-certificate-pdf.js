/**
 * Professional Certificate PDF Generator
 * نسخه نهایی و حرفه‌ای برای تولید PDF گواهی‌نامه
 * 
 * ویژگی‌ها:
 * ✅ اندازه ثابت در همه مانیتورها (مستقل از DPI و Resolution)
 * ✅ فونت‌های فارسی کاملاً درست (با dom-to-image-more)
 * ✅ کیفیت بالا و حجم بهینه
 * ✅ پشتیبانی از چند صفحه و جهت‌گیری مختلف
 * 
 * @version 4.0.0
 * @author PTP System
 */

class ProfessionalCertificatePdf {
    constructor(options = {}) {
        this.options = {
            // A4 dimensions - FIXED (مستقل از مانیتور)
            pageWidth: 210,        // mm
            pageHeight: 297,       // mm
            dpi: 300,              // Standard print DPI
            quality: 0.95,         // Image quality
            imageFormat: 'PNG',    // PNG for better quality
            compress: true,        // Compress PDF
            debug: options.debug || false,
            ...options
        };

        // محاسبه pixel از mm (ثابت برای همه مانیتورها)
        this.pixelWidth = this.mmToPixel(this.options.pageWidth);
        this.pixelHeight = this.mmToPixel(this.options.pageHeight);

        this.domToImageLoaded = false;
        
        this.log('🚀 Professional Certificate PDF Generator initialized');
        this.log(`📐 Page size: ${this.options.pageWidth}x${this.options.pageHeight}mm = ${this.pixelWidth}x${this.pixelHeight}px`);
    }

    /**
     * تبدیل mm به pixel (مستقل از مانیتور)
     */
    mmToPixel(mm) {
        // استاندارد: 1 inch = 25.4 mm
        const inches = mm / 25.4;
        const pixels = Math.round(inches * this.options.dpi);
        return pixels;
    }

    /**
     * تبدیل pixel به mm
     */
    pixelToMm(pixels) {
        const inches = pixels / this.options.dpi;
        const mm = inches * 25.4;
        return mm;
    }

    log(...args) {
        if (this.options.debug) {
            console.log('[Professional PDF]', ...args);
        }
    }

    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * بارگذاری کتابخانه dom-to-image-more
     */
    async loadDomToImageMore() {
        if (window.domtoimage) {
            this.domToImageLoaded = true;
            this.log('✅ dom-to-image-more already loaded');
            return true;
        }

        this.log('⏳ Loading dom-to-image-more library...');
        
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            // استفاده از dom-to-image-more که فونت‌های فارسی رو بهتر پشتیبانی می‌کنه
            script.src = 'https://cdn.jsdelivr.net/npm/dom-to-image-more@2.9.0/dist/dom-to-image-more.min.js';
            
            script.onload = () => {
                this.domToImageLoaded = true;
                this.log('✅ dom-to-image-more loaded successfully');
                resolve(true);
            };
            
            script.onerror = () => {
                this.log('❌ Failed to load dom-to-image-more, trying fallback...');
                // Fallback به نسخه معمولی
                const fallbackScript = document.createElement('script');
                fallbackScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js';
                fallbackScript.onload = () => {
                    this.domToImageLoaded = true;
                    this.log('✅ dom-to-image loaded (fallback)');
                    resolve(true);
                };
                fallbackScript.onerror = () => {
                    reject(new Error('Failed to load dom-to-image library'));
                };
                document.head.appendChild(fallbackScript);
            };
            
            document.head.appendChild(script);
        });
    }

    /**
     * آماده‌سازی المنت برای capture با اندازه ثابت
     */
    prepareElement(element, width, height) {
        this.log(`📏 Preparing element: ${width}x${height}px`);
        
        // ذخیره استایل‌های اصلی
        const originalStyles = {
            width: element.style.width,
            height: element.style.height,
            minWidth: element.style.minWidth,
            minHeight: element.style.minHeight,
            maxWidth: element.style.maxWidth,
            maxHeight: element.style.maxHeight,
            transform: element.style.transform,
            transformOrigin: element.style.transformOrigin,
            overflow: element.style.overflow,
            position: element.style.position,
            left: element.style.left,
            top: element.style.top
        };

        // تنظیم اندازه دقیق (مستقل از viewport)
        element.style.width = width + 'px';
        element.style.height = height + 'px';
        element.style.minWidth = width + 'px';
        element.style.minHeight = height + 'px';
        element.style.maxWidth = width + 'px';
        element.style.maxHeight = height + 'px';
        element.style.transform = 'scale(1)';
        element.style.transformOrigin = 'top left';
        element.style.overflow = 'hidden';
        element.style.position = 'relative';
        element.style.left = '0';
        element.style.top = '0';

        return originalStyles;
    }

    /**
     * بازگرداندن استایل‌های اصلی المنت
     */
    restoreElement(element, originalStyles) {
        Object.keys(originalStyles).forEach(key => {
            if (originalStyles[key] !== null && originalStyles[key] !== undefined) {
                element.style[key] = originalStyles[key];
            }
        });
    }

    /**
     * Capture یک صفحه با dom-to-image-more
     */
    async capturePage(pageElement, pageIndex, totalPages) {
        this.log(`📸 Capturing page ${pageIndex + 1}/${totalPages}...`);

        // تشخیص orientation
        const isLandscape = pageElement.classList.contains('certificate-orientation-landscape');
        const width = isLandscape ? this.pixelHeight : this.pixelWidth;
        const height = isLandscape ? this.pixelWidth : this.pixelHeight;

        this.log(`📐 Orientation: ${isLandscape ? 'landscape' : 'portrait'} (${width}x${height}px)`);

        // آماده‌سازی المنت
        const originalStyles = this.prepareElement(pageElement, width, height);

        // انتظار برای render شدن
        await this.wait(500);

        try {
            // Capture با dom-to-image-more
            const dataUrl = await domtoimage.toPng(pageElement, {
                width: width,
                height: height,
                quality: this.options.quality,
                bgcolor: '#ffffff',
                style: {
                    'transform': 'scale(1)',
                    'transform-origin': 'top left',
                    'font-smoothing': 'antialiased',
                    '-webkit-font-smoothing': 'antialiased',
                    'text-rendering': 'optimizeLegibility'
                },
                filter: (node) => {
                    // حذف المنت‌های ناخواسته
                    if (node.id === 'downloadPdfBtn') return false;
                    if (node.classList && node.classList.contains('floating-pdf-controls')) return false;
                    if (node.classList && node.classList.contains('certificate-page-header')) return false;
                    if (node.classList && node.classList.contains('certificate-page-badges')) return false;
                    return true;
                }
            });

            this.log(`✅ Page ${pageIndex + 1} captured successfully`);

            return {
                dataUrl: dataUrl,
                width: this.pixelToMm(width),
                height: this.pixelToMm(height),
                orientation: isLandscape ? 'landscape' : 'portrait'
            };

        } catch (error) {
            this.log(`❌ Failed to capture page ${pageIndex + 1}:`, error);
            throw error;
        } finally {
            // بازگرداندن استایل‌های اصلی
            this.restoreElement(pageElement, originalStyles);
        }
    }

    /**
     * انتظار برای بارگذاری فونت‌ها
     */
    async waitForFonts() {
        this.log('⏳ Waiting for fonts...');
        
        if (document.fonts && document.fonts.ready) {
            try {
                await document.fonts.ready;
                this.log('✅ Fonts loaded');
                
                // انتظار اضافی برای رندر کامل فونت‌های فارسی
                await this.wait(1000);
                
                // تست فونت
                const testDiv = document.createElement('div');
                testDiv.style.cssText = 'position:absolute;left:-9999px;opacity:0;font-size:16px;';
                testDiv.innerHTML = 'تست فونت فارسی Test Font 0123456789';
                document.body.appendChild(testDiv);
                testDiv.offsetHeight; // Force reflow
                await this.wait(300);
                document.body.removeChild(testDiv);
                
                this.log('✅ Font rendering completed');
            } catch (e) {
                this.log('⚠️ Font loading warning:', e);
            }
        }
    }

    /**
     * تولید PDF از صفحات
     */
    async generate(selector = '.certificate-preview-page', filename = 'certificate.pdf') {
        try {
            this.log('🚀 Starting PDF generation...');
            this.log(`📋 Selector: ${selector}`);

            // بارگذاری dom-to-image-more
            await this.loadDomToImageMore();

            // انتظار برای فونت‌ها
            await this.waitForFonts();

            // پیدا کردن صفحات
            const pages = document.querySelectorAll(selector);
            
            if (!pages || pages.length === 0) {
                throw new Error(`هیچ صفحه‌ای با selector "${selector}" پیدا نشد!`);
            }

            this.log(`📄 Found ${pages.length} page(s)`);

            // چک کردن jsPDF
            if (!window.jspdf || typeof window.jspdf.jsPDF !== 'function') {
                throw new Error('jsPDF library not loaded!');
            }

            // Capture تمام صفحات
            const capturedPages = [];
            
            for (let i = 0; i < pages.length; i++) {
                const pageData = await this.capturePage(pages[i], i, pages.length);
                capturedPages.push(pageData);
                
                // استراحت کوتاه بین صفحات
                if (i < pages.length - 1) {
                    await this.wait(200);
                }
            }

            // ساخت PDF
            this.log('📝 Creating PDF document...');
            
            const firstPage = capturedPages[0];
            const pdf = new window.jspdf.jsPDF({
                orientation: firstPage.orientation,
                unit: 'mm',
                format: [firstPage.width, firstPage.height],
                compress: this.options.compress,
                precision: 2
            });

            // اضافه کردن صفحات به PDF
            for (let i = 0; i < capturedPages.length; i++) {
                const page = capturedPages[i];
                
                if (i > 0) {
                    pdf.addPage([page.width, page.height], page.orientation);
                }
                
                pdf.addImage(
                    page.dataUrl,
                    this.options.imageFormat,
                    0,
                    0,
                    page.width,
                    page.height,
                    undefined,
                    'FAST'
                );
                
                this.log(`✅ Page ${i + 1}/${capturedPages.length} added to PDF`);
            }

            // ذخیره PDF
            pdf.save(filename);
            
            this.log('✅ PDF saved successfully!');
            this.log(`📦 File: ${filename}`);
            this.log(`📊 Pages: ${capturedPages.length}`);

            return {
                success: true,
                filename: filename,
                pages: capturedPages.length,
                size: `${this.options.pageWidth}x${this.options.pageHeight}mm`
            };

        } catch (error) {
            this.log('❌ PDF generation failed:', error);
            throw error;
        }
    }

    /**
     * Quick generate - تولید سریع PDF
     */
    static async quickGenerate(options = {}) {
        const generator = new ProfessionalCertificatePdf(options);
        return await generator.generate(options.selector, options.filename);
    }
}

// Export به window
window.ProfessionalCertificatePdf = ProfessionalCertificatePdf;

console.log('✅ Professional Certificate PDF Generator loaded successfully!');
console.log('📦 Version: 4.0.0');
console.log('🎯 Features: Fixed size, Perfect fonts, Multi-page support');
