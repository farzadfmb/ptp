/**
 * Certificate PDF Generator V2
 * حل مشکل فونت فارسی با استفاده از روش SVG
 * 
 * @version 2.1.0
 */

class CertificatePdfGeneratorV2 {
    constructor(options = {}) {
        this.options = {
            dpi: options.dpi || 96,
            quality: options.quality || 0.85,
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
            }
        };

        this.log('PDF Generator V2 initialized');
    }

    log(...args) {
        if (this.options.debug) {
            console.log('[PDFGen V2]', ...args);
        }
    }

    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    mmToPixels(mm) {
        return Math.round((mm * this.options.dpi) / 25.4);
    }

    /**
     * Load all fonts before capture
     */
    async loadFonts() {
        this.log('Loading fonts...');
        
        if (document.fonts && document.fonts.ready) {
            await document.fonts.ready;
            await this.wait(1000);
        }
        
        // Force font rendering by creating hidden element with Arial
        const testDiv = document.createElement('div');
        testDiv.style.cssText = 'position:absolute;left:-9999px;font-family:Arial,"B Nazanin";font-size:16px;font-weight:normal;';
        testDiv.innerHTML = 'تست فونت فارسی Persian Font Test ABCDEF 0123456789 ابجدهوز';
        document.body.appendChild(testDiv);
        testDiv.offsetHeight; // Force reflow
        await this.wait(800);
        document.body.removeChild(testDiv);
        
        this.log('Fonts loaded and tested');
    }

    /**
     * Capture element using html2canvas with optimized settings
     */
    async captureWithCanvas(element, width, height) {
        this.log('Capturing with html2canvas...');
        
        // Store original styles
        const originalStyles = {
            width: element.style.width,
            height: element.style.height,
            position: element.style.position,
            left: element.style.left,
            top: element.style.top
        };
        
        try {
            // Set exact dimensions
            element.style.width = width + 'px';
            element.style.height = height + 'px';
            element.style.position = 'relative';
            element.style.left = '0';
            element.style.top = '0';
            
            // Force all text elements to use Tahoma (reliable Persian font)
            const allElements = element.querySelectorAll('*');
            const fontOverrides = new Map();
            
            this.log(`Found ${allElements.length} elements to process`);
            
            allElements.forEach(el => {
                // Store original styles
                const computed = window.getComputedStyle(el);
                const originalFont = el.style.fontFamily;
                
                fontOverrides.set(el, {
                    fontFamily: originalFont,
                    fontSize: el.style.fontSize,
                    fontWeight: el.style.fontWeight
                });
                
                // Try multiple Persian-compatible fonts in order
                // Arial is universal and works best with html2canvas
                el.style.setProperty('font-family', 'Arial, "B Nazanin", "Segoe UI", sans-serif', 'important');
                el.style.fontSize = computed.fontSize;
                el.style.fontWeight = computed.fontWeight;
                el.style.lineHeight = computed.lineHeight;
            });
            
            this.log('✅ All fonts set to Arial');
            
            // Wait longer for font rendering
            await this.wait(800);
            
            const canvas = await html2canvas(element, {
                scale: 2,  // Maximum scale for best text quality
                width: width,
                height: height,
                windowWidth: width,
                windowHeight: height,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                logging: this.options.debug,
                letterRendering: true,  // Better for Persian text
                foreignObjectRendering: false,  // More compatible with fonts
                imageTimeout: 0,
                removeContainer: true,
                onclone: (clonedDoc) => {
                    // Force Arial in cloned document too
                    const style = clonedDoc.createElement('style');
                    style.textContent = `
                        * {
                            font-family: Arial, "B Nazanin", "Segoe UI", sans-serif !important;
                        }
                    `;
                    clonedDoc.head.appendChild(style);
                    this.log('✅ Arial applied to cloned document');
                },
                ignoreElements: (el) => {
                    return el.id === 'downloadPdfBtn' || 
                           el.classList.contains('floating-pdf-controls') ||
                           el.classList.contains('certificate-page-header');
                }
            });
            
            // Restore original fonts
            fontOverrides.forEach((styles, el) => {
                el.style.fontFamily = styles.fontFamily;
                el.style.fontSize = styles.fontSize;
                el.style.fontWeight = styles.fontWeight;
            });
            
            const imageData = canvas.toDataURL('image/jpeg', this.options.quality);
            
            // Cleanup
            canvas.width = 0;
            canvas.height = 0;
            
            return imageData;
            
        } finally {
            // Restore original element styles
            Object.keys(originalStyles).forEach(key => {
                element.style[key] = originalStyles[key];
            });
        }
    }

    /**
     * Generate PDF from certificate pages
     */
    async generate(selector = '.certificate-preview-page', filename = 'certificate.pdf') {
        try {
            this.log('Starting PDF generation...');
            
            // Load fonts first
            await this.loadFonts();
            
            const pages = document.querySelectorAll(selector);
            if (!pages || pages.length === 0) {
                throw new Error('No pages found');
            }
            
            this.log(`Found ${pages.length} page(s)`);
            
            // Check libraries
            if (typeof html2canvas !== 'function') {
                throw new Error('html2canvas not loaded');
            }
            if (!window.jspdf || typeof window.jspdf.jsPDF !== 'function') {
                throw new Error('jsPDF not loaded');
            }
            
            // Process pages
            const pageImages = [];
            
            for (let i = 0; i < pages.length; i++) {
                const page = pages[i];
                this.log(`Processing page ${i + 1}/${pages.length}...`);
                
                const isLandscape = page.classList.contains('certificate-orientation-landscape');
                const orientation = isLandscape ? 'landscape' : 'portrait';
                
                const format = this.formats[this.options.format];
                const dimensions = format[orientation];
                const width = this.mmToPixels(dimensions.width);
                const height = this.mmToPixels(dimensions.height);
                
                const imageData = await this.captureWithCanvas(page, width, height);
                
                pageImages.push({
                    data: imageData,
                    orientation: orientation,
                    width: dimensions.width,
                    height: dimensions.height
                });
                
                this.log(`Page ${i + 1} captured`);
            }
            
            // Create PDF
            this.log('Creating PDF...');
            const firstPage = pageImages[0];
            const pdf = new window.jspdf.jsPDF({
                orientation: firstPage.orientation,
                unit: 'mm',
                format: [firstPage.width, firstPage.height],
                compress: true
            });
            
            for (let i = 0; i < pageImages.length; i++) {
                const pageImg = pageImages[i];
                
                if (i > 0) {
                    pdf.addPage([pageImg.width, pageImg.height], pageImg.orientation);
                }
                
                pdf.addImage(
                    pageImg.data,
                    'JPEG',
                    0,
                    0,
                    pageImg.width,
                    pageImg.height,
                    undefined,
                    'FAST'
                );
            }
            
            // Save
            pdf.save(filename);
            
            this.log('✅ PDF generated successfully');
            
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
}

// Export
window.CertificatePdfGeneratorV2 = CertificatePdfGeneratorV2;

console.log('✅ CertificatePdfGeneratorV2 loaded successfully');
