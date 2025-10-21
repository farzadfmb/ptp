/**
 * Certificate PDF Generator - Final Version
 * حل قطعی مشکل فونت فارسی با استفاده از dom-to-image
 * 
 * این نسخه از dom-to-image استفاده می‌کنه که فونت‌ها رو به SVG تبدیل می‌کنه
 * و مشکل فونت فارسی رو کاملاً حل می‌کنه
 * 
 * @version 3.0.0
 */

class CertificatePdfFinal {
    constructor(options = {}) {
        this.options = {
            quality: options.quality || 0.92,
            format: options.format || 'a4',
            orientation: options.orientation || 'portrait',
            margin: options.margin || 0,
            debug: options.debug || false,
            ...options
        };

        // A4 dimensions in pixels (at 96 DPI)
        this.formats = {
            a4: {
                portrait: { width: 794, height: 1123 },  // 210mm x 297mm at 96 DPI
                landscape: { width: 1123, height: 794 }
            }
        };

        this.log('📦 Certificate PDF Final Generator initialized');
    }

    log(...args) {
        if (this.options.debug) {
            console.log('[PDF Final]', ...args);
        }
    }

    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Load dom-to-image library dynamically if not present
     */
    async loadDomToImage() {
        if (window.domtoimage) {
            this.log('✅ domtoimage already loaded');
            return true;
        }

        this.log('⏳ Loading domtoimage library...');
        
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js';
            script.onload = () => {
                this.log('✅ domtoimage loaded successfully');
                resolve(true);
            };
            script.onerror = () => {
                this.log('❌ Failed to load domtoimage');
                reject(new Error('Failed to load domtoimage library'));
            };
            document.head.appendChild(script);
        });
    }

    /**
     * Prepare element for capture
     */
    prepareElement(element, width, height) {
        const originalStyles = {
            width: element.style.width,
            height: element.style.height,
            maxWidth: element.style.maxWidth,
            maxHeight: element.style.maxHeight,
            overflow: element.style.overflow,
            position: element.style.position,
            transform: element.style.transform
        };

        // Set exact dimensions
        element.style.width = width + 'px';
        element.style.height = height + 'px';
        element.style.maxWidth = width + 'px';
        element.style.maxHeight = height + 'px';
        element.style.overflow = 'hidden';
        element.style.position = 'relative';
        element.style.transform = 'scale(1)';

        return originalStyles;
    }

    /**
     * Restore element styles
     */
    restoreElement(element, originalStyles) {
        Object.keys(originalStyles).forEach(key => {
            element.style[key] = originalStyles[key];
        });
    }

    /**
     * Capture page using dom-to-image
     */
    async capturePage(pageElement, orientation) {
        this.log('📸 Capturing page...');

        const format = this.formats[this.options.format];
        const dimensions = format[orientation];
        const width = dimensions.width;
        const height = dimensions.height;

        this.log(`Page size: ${width}x${height}px`);

        // Prepare element
        const originalStyles = this.prepareElement(pageElement, width, height);

        // Wait for layout
        await this.wait(500);

        try {
            // Use domtoimage to capture (preserves fonts perfectly)
            const dataUrl = await window.domtoimage.toPng(pageElement, {
                width: width,
                height: height,
                quality: this.options.quality,
                style: {
                    transform: 'scale(1)',
                    transformOrigin: 'top left'
                },
                filter: (node) => {
                    // Skip floating controls
                    if (node.id === 'downloadPdfBtn') return false;
                    if (node.classList && node.classList.contains('floating-pdf-controls')) return false;
                    if (node.classList && node.classList.contains('certificate-page-header')) return false;
                    return true;
                }
            });

            this.log('✅ Page captured successfully');
            return dataUrl;

        } catch (error) {
            this.log('❌ Capture failed:', error);
            throw error;
        } finally {
            // Restore original styles
            this.restoreElement(pageElement, originalStyles);
        }
    }

    /**
     * Generate PDF from pages
     */
    async generate(selector = '.certificate-preview-page', filename = 'certificate.pdf') {
        try {
            this.log('🚀 Starting PDF generation...');

            // Load dom-to-image if needed
            await this.loadDomToImage();

            // Wait for fonts
            if (document.fonts && document.fonts.ready) {
                await document.fonts.ready;
                await this.wait(1000);
            }

            // Find pages
            const pages = document.querySelectorAll(selector);
            if (!pages || pages.length === 0) {
                throw new Error('No pages found with selector: ' + selector);
            }

            this.log(`Found ${pages.length} page(s)`);

            // Check jsPDF
            if (!window.jspdf || typeof window.jspdf.jsPDF !== 'function') {
                throw new Error('jsPDF library not loaded!');
            }

            // Process each page
            const pageImages = [];

            for (let i = 0; i < pages.length; i++) {
                const page = pages[i];
                this.log(`Processing page ${i + 1}/${pages.length}...`);

                const isLandscape = page.classList.contains('certificate-orientation-landscape');
                const orientation = isLandscape ? 'landscape' : 'portrait';

                const imageData = await this.capturePage(page, orientation);

                const format = this.formats[this.options.format];
                const dimensions = format[orientation];

                pageImages.push({
                    data: imageData,
                    orientation: orientation,
                    width: dimensions.width,
                    height: dimensions.height
                });

                this.log(`✅ Page ${i + 1} captured`);
            }

            // Create PDF
            this.log('📄 Creating PDF document...');

            const firstPage = pageImages[0];
            
            // Convert pixels to mm (96 DPI)
            const widthMm = (firstPage.width * 25.4) / 96;
            const heightMm = (firstPage.height * 25.4) / 96;

            const pdf = new window.jspdf.jsPDF({
                orientation: firstPage.orientation,
                unit: 'mm',
                format: [widthMm, heightMm],
                compress: true
            });

            for (let i = 0; i < pageImages.length; i++) {
                const pageImg = pageImages[i];
                const pageWidthMm = (pageImg.width * 25.4) / 96;
                const pageHeightMm = (pageImg.height * 25.4) / 96;

                if (i > 0) {
                    pdf.addPage([pageWidthMm, pageHeightMm], pageImg.orientation);
                }

                pdf.addImage(
                    pageImg.data,
                    'PNG',
                    0,
                    0,
                    pageWidthMm,
                    pageHeightMm,
                    undefined,
                    'FAST'
                );
            }

            // Save
            pdf.save(filename);

            this.log('✅ PDF saved successfully!');

            return {
                success: true,
                filename: filename,
                pages: pageImages.length
            };

        } catch (error) {
            this.log('❌ Error:', error);
            throw error;
        }
    }

    /**
     * Quick generate method
     */
    static async quickGenerate(options = {}) {
        const generator = new CertificatePdfFinal(options);
        return await generator.generate(options.selector, options.filename);
    }
}

// Export to window
window.CertificatePdfFinal = CertificatePdfFinal;

console.log('✅ CertificatePdfFinal loaded successfully!');
