/**
 * Certificate PDF Generator
 * نسخه حرفه‌ای برای تبدیل گواهی‌نامه به PDF با کیفیت بالا
 * 
 * ویژگی‌ها:
 * - پشتیبانی کامل از RTL و فارسی
 * - کیفیت بالا (300 DPI)
 * - حفظ دقیق استایل‌ها
 * - بدون وابستگی به کتابخانه خارجی
 * - بهینه‌سازی حافظه
 * 
 * @version 2.0.0
 * @author PTP System
 */

class CertificatePdfGenerator {
    constructor(options = {}) {
        this.options = {
            dpi: options.dpi || 300,
            quality: options.quality || 1.0,
            format: options.format || 'a4',
            orientation: options.orientation || 'portrait',
            margin: options.margin || 0,
            debug: options.debug || false,
            ...options
        };

        // A4 dimensions in mm
        this.formats = {
            a4: {
                portrait: { width: 210, height: 297 },
                landscape: { width: 297, height: 210 }
            },
            a5: {
                portrait: { width: 148, height: 210 },
                landscape: { width: 210, height: 148 }
            }
        };

        this.log('Certificate PDF Generator initialized', this.options);
    }

    /**
     * Log message for debugging
     */
    log(...args) {
        if (this.options.debug) {
            console.log('[CertificatePDF]', ...args);
        }
    }

    /**
     * Convert mm to pixels at specific DPI
     */
    mmToPixels(mm) {
        return Math.round((mm * this.options.dpi) / 25.4);
    }

    /**
     * Wait for fonts to load completely
     */
    async waitForFonts() {
        if (document.fonts && document.fonts.ready) {
            this.log('Waiting for fonts to load...');
            
            try {
                await document.fonts.ready;
                
                // Check specifically for Vazirmatn font
                let vazirLoaded = false;
                try {
                    vazirLoaded = await document.fonts.check('1em Vazirmatn');
                } catch (e) {
                    vazirLoaded = false;
                }
                
                this.log('Vazirmatn font loaded:', vazirLoaded);
                
                if (!vazirLoaded) {
                    this.log('⚠️ Warning: Vazirmatn not loaded. Using Tahoma as fallback for Persian.');
                    // Set flag to use Tahoma
                    this.useTahomaFallback = true;
                }
                
                // Extra wait for Persian fonts to fully render
                await this.wait(1200);
                
                this.log('✅ All fonts ready');
            } catch (e) {
                this.log('Font loading check error:', e);
                this.useTahomaFallback = true;
                await this.wait(1500);
            }
        }
    }

    /**
     * Generate PDF from certificate pages
     */
    async generate(selector = '.certificate-preview-page', filename = 'certificate.pdf') {
        try {
            this.log('Starting PDF generation...');

            // Wait for all fonts to load (important for Persian text)
            await this.waitForFonts();

            // Find all certificate pages
            const pages = document.querySelectorAll(selector);
            
            if (!pages || pages.length === 0) {
                throw new Error('No certificate pages found with selector: ' + selector);
            }

            this.log(`Found ${pages.length} page(s)`);

            // Check if jsPDF is available
            if (!window.jspdf || typeof window.jspdf.jsPDF !== 'function') {
                throw new Error('jsPDF library not loaded!');
            }

            // Check if html2canvas is available
            if (typeof html2canvas !== 'function') {
                throw new Error('html2canvas library not loaded!');
            }

            // Process each page
            const pageImages = [];
            
            for (let i = 0; i < pages.length; i++) {
                const page = pages[i];
                this.log(`Processing page ${i + 1}/${pages.length}...`);
                
                // Determine orientation
                const isLandscape = page.classList.contains('certificate-orientation-landscape');
                const orientation = isLandscape ? 'landscape' : 'portrait';
                
                // Capture page as image
                const imageData = await this.capturePage(page, orientation);
                
                pageImages.push({
                    data: imageData,
                    orientation: orientation
                });
                
                this.log(`Page ${i + 1} captured successfully`);
            }

            // Create PDF
            this.log('Creating PDF document...');
            const pdf = await this.createPdfDocument(pageImages);

            // Save PDF
            this.log(`Saving PDF as: ${filename}`);
            pdf.save(filename);

            // Cleanup
            this.cleanup(pageImages);

            this.log('PDF generation completed successfully!');
            
            return {
                success: true,
                filename: filename,
                pages: pageImages.length
            };

        } catch (error) {
            this.log('Error during PDF generation:', error);
            throw error;
        }
    }

    /**
     * Force fonts to render by creating hidden text elements
     */
    async ensureFontsRendered(element) {
        const testDiv = document.createElement('div');
        testDiv.style.cssText = 'position:absolute;left:-9999px;top:-9999px;font-family:Vazirmatn,sans-serif;';
        testDiv.textContent = 'آزمایش فونت فارسی ABCDEFG 0123456789';
        document.body.appendChild(testDiv);
        
        // Force layout recalculation
        testDiv.offsetHeight;
        
        await this.wait(100);
        document.body.removeChild(testDiv);
    }

    /**
     * Capture a single page as high-quality image
     */
    async capturePage(pageElement, orientation) {
        // Ensure fonts are rendered
        await this.ensureFontsRendered(pageElement);
        
        // Get dimensions for this page
        const format = this.formats[this.options.format];
        const dimensions = format[orientation];
        
        // Convert to pixels at target DPI
        const width = this.mmToPixels(dimensions.width);
        const height = this.mmToPixels(dimensions.height);

        this.log(`Capturing page: ${width}x${height}px at ${this.options.dpi} DPI`);

        // Store original styles
        const originalStyles = {
            width: pageElement.style.width,
            height: pageElement.style.height,
            minWidth: pageElement.style.minWidth,
            minHeight: pageElement.style.minHeight,
            maxWidth: pageElement.style.maxWidth,
            maxHeight: pageElement.style.maxHeight,
            position: pageElement.style.position,
            left: pageElement.style.left,
            top: pageElement.style.top
        };

        try {
            // Set exact dimensions for capture
            pageElement.style.width = width + 'px';
            pageElement.style.height = height + 'px';
            pageElement.style.minWidth = width + 'px';
            pageElement.style.minHeight = height + 'px';
            pageElement.style.maxWidth = width + 'px';
            pageElement.style.maxHeight = height + 'px';
            pageElement.style.position = 'relative';
            pageElement.style.left = '0';
            pageElement.style.top = '0';

            // Wait for layout to settle and fonts to render
            await this.wait(500);

            // Calculate scale for better text quality (especially Persian fonts)
            // Lower scale = much smaller file size
            const scaleFactor = 1.2; // Minimal scale for smaller files

            // Capture with html2canvas with Persian/RTL support
            const canvas = await html2canvas(pageElement, {
                scale: scaleFactor,
                width: width,
                height: height,
                windowWidth: width,
                windowHeight: height,
                useCORS: true,
                allowTaint: true,  // Allow cross-origin for web fonts
                backgroundColor: '#ffffff',
                logging: this.options.debug,
                imageTimeout: 0,
                removeContainer: true,
                // Persian and RTL support - CRITICAL for Farsi text
                letterRendering: true,
                foreignObjectRendering: false,
                // Ensure proper text rendering
                onclone: (clonedDoc) => {
                    // Determine which font to use
                    const fontFamily = this.useTahomaFallback 
                        ? 'Tahoma, Arial, sans-serif' 
                        : 'Vazirmatn, Tahoma, Arial, sans-serif';
                    
                    this.log('Using font:', fontFamily);
                    
                    // Force all text elements to use proper Persian font
                    const allElements = clonedDoc.body.querySelectorAll('*');
                    allElements.forEach(el => {
                        const computedStyle = window.getComputedStyle(el);
                        
                        // Apply font inline - especially for Persian text
                        if (computedStyle.fontFamily.includes('Vazir') || el.textContent.match(/[\u0600-\u06FF]/)) {
                            el.style.fontFamily = fontFamily;
                            el.style.fontWeight = computedStyle.fontWeight;
                            el.style.fontSize = computedStyle.fontSize;
                            el.style.lineHeight = computedStyle.lineHeight;
                        }
                    });
                    
                    // Add font face definition
                    const style = clonedDoc.createElement('style');
                    style.textContent = `
                        @import url('https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css');
                        body, * {
                            font-family: ${fontFamily} !important;
                            -webkit-font-smoothing: antialiased;
                            -moz-osx-font-smoothing: grayscale;
                            text-rendering: optimizeLegibility;
                        }
                    `;
                    clonedDoc.head.appendChild(style);
                    
                    // Force layout recalculation
                    clonedDoc.body.offsetHeight;
                },
                // Skip certain elements
                ignoreElements: (element) => {
                    // Skip floating controls
                    if (element.id === 'downloadPdfBtn') return true;
                    if (element.classList && element.classList.contains('floating-pdf-controls')) return true;
                    if (element.classList && element.classList.contains('certificate-page-header')) return true;
                    return false;
                }
            });

            this.log(`Canvas captured: ${canvas.width}x${canvas.height}px`);

            // Convert to high-quality image data
            const imageData = canvas.toDataURL('image/png', this.options.quality);

            // Cleanup canvas
            canvas.width = 0;
            canvas.height = 0;

            return imageData;

        } finally {
            // Restore original styles
            Object.keys(originalStyles).forEach(key => {
                pageElement.style[key] = originalStyles[key];
            });
        }
    }

    /**
     * Create PDF document from captured images
     */
    async createPdfDocument(pageImages) {
        const { jsPDF } = window.jspdf;

        // Use first page orientation for PDF
        const firstOrientation = pageImages[0].orientation;
        
        // Get dimensions
        const format = this.formats[this.options.format];
        const dimensions = format[firstOrientation];

        this.log(`Creating PDF: ${this.options.format.toUpperCase()} ${firstOrientation}`);

        // Create PDF
        const pdf = new jsPDF({
            orientation: firstOrientation,
            unit: 'mm',
            format: this.options.format
        });

        // Add each page
        for (let i = 0; i < pageImages.length; i++) {
            const pageImage = pageImages[i];
            
            // Add new page for subsequent pages
            if (i > 0) {
                // Check if orientation changed
                if (pageImage.orientation !== pageImages[i - 1].orientation) {
                    // Add page with different orientation
                    const newDimensions = format[pageImage.orientation];
                    pdf.addPage(this.options.format, pageImage.orientation);
                } else {
                    pdf.addPage();
                }
            }

            // Get current page dimensions
            const pageDimensions = format[pageImage.orientation];
            const pageWidth = pageDimensions.width;
            const pageHeight = pageDimensions.height;

            // Calculate content area (with margins)
            const margin = this.options.margin;
            const contentWidth = pageWidth - (margin * 2);
            const contentHeight = pageHeight - (margin * 2);

            // Add image to PDF
            pdf.addImage(
                pageImage.data,
                'PNG',
                margin,
                margin,
                contentWidth,
                contentHeight,
                undefined,
                'FAST'
            );

            this.log(`Added page ${i + 1} to PDF`);
        }

        return pdf;
    }

    /**
     * Wait for specified milliseconds
     */
    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Cleanup memory
     */
    cleanup(pageImages) {
        this.log('Cleaning up memory...');
        
        // Clear image data
        pageImages.forEach((page, index) => {
            pageImages[index].data = null;
        });
        
        pageImages.length = 0;

        // Suggest garbage collection if available
        if (window.gc) {
            window.gc();
        }
    }

    /**
     * Static method to quickly generate PDF
     */
    static async quickGenerate(options = {}) {
        const generator = new CertificatePdfGenerator(options);
        return await generator.generate(
            options.selector || '.certificate-preview-page',
            options.filename || 'certificate.pdf'
        );
    }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CertificatePdfGenerator;
}

// Make available globally
if (typeof window !== 'undefined') {
    window.CertificatePdfGenerator = CertificatePdfGenerator;
}
