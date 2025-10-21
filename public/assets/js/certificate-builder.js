/**
 * Certificate Builder - Advanced Certificate Designer
 * 
 * Supported Field Types:
 * - text: Simple text input
 * - textarea: Multi-line text input
 * - number: Numeric input with min/max/step
 * - select: Dropdown select
 * - multi_select: Multiple selection
 * - toggle: Boolean toggle switch
 * - color: Color picker with hex input
 * - font_size: Font size selector with preset sizes (8-72px) and custom input (6-200px)
 * - table_editor: Table data editor
 * - image_upload: Image uploader
 * 
 * Font Size Field Example:
 * {
 *   key: 'fontSize',
 *   label: 'اندازه متن',
 *   type: 'font_size'
 * }
 * 
 * This provides:
 * - 16 preset sizes: 8, 10, 12, 14, 16, 18, 20, 24, 28, 32, 36, 42, 48, 56, 64, 72
 * - Custom input range: 6px to 200px
 * - Visual preset buttons with active state
 * - Real-time synchronization between presets and custom input
 */
(function () {
    var root = document.getElementById('certificate-builder-root');
    if (!root) {
        return;
    }

    var stateInput = document.getElementById('builder-state-input');
    var pageListEl = root.querySelector('[data-role="page-list"]');
    var componentListEl = root.querySelector('[data-role="component-list"]');
    var dropZoneEl = root.querySelector('[data-role="drop-zone"]');
    var dropPlaceholderEl = root.querySelector('[data-role="drop-placeholder"]');
    var canvasElementsEl = root.querySelector('[data-role="canvas-elements"]');
    var pageMetaEl = root.querySelector('[data-role="page-meta"]');
    var pageTemplateEl = root.querySelector('[data-role="page-template"]');
    var pageLayoutControlsEl = root.querySelector('[data-role="page-layout-controls"]');
    var elementSettingsModal = document.getElementById('builder-element-settings-modal');
    var elementSettingsModalBody = elementSettingsModal ? elementSettingsModal.querySelector('[data-role="element-settings-modal-body"]') : null;
    var elementSettingsModalTitle = elementSettingsModal ? elementSettingsModal.querySelector('[data-role="element-settings-modal-title"]') : null;
    var elementSettingsApplyButton = elementSettingsModal ? elementSettingsModal.querySelector('[data-role="element-settings-apply"]') : null;
    var elementSettingsModalInstance = null;
    var isModalFallback = false;
    var addPageButton = root.querySelector('[data-action="add-page"]');
    var uploadEndpoint = root.dataset.uploadEndpoint || '';
    var csrfTokenInput = document.querySelector('#certificate-builder-form input[name="_token"]');
    var csrfToken = csrfTokenInput ? csrfTokenInput.value : '';

    var parseJsonSafe = function (payload, fallback) {
        if (!payload || typeof payload !== 'string') {
            return fallback;
        }
        try {
            return JSON.parse(payload);
        } catch (error) {
            return fallback;
        }
    };

    var generateId = function (prefix) {
        var random = Math.random().toString(36).slice(2, 10);
        return prefix + '-' + random;
    };

    var escapeHtml = function (value) {
        if (value === null || value === undefined) {
            return '';
        }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    var truncateText = function (value, limit) {
        if (!value) {
            return '';
        }
        var stringValue = String(value);
        if (stringValue.length <= limit) {
            return stringValue;
        }
        return stringValue.slice(0, Math.max(0, limit - 1)) + '…';
    };

    var normalizeBoolean = function (value) {
        return value === true || value === 'true' || value === 1 || value === '1' || value === 'on';
    };

    var normalizeTableData = function (value) {
        var maxColumns = 8;
        var maxRows = 20;
        var columns = [];
        var rows = [];
        if (value && typeof value === 'object') {
            if (Array.isArray(value.columns)) {
                value.columns.forEach(function (item) {
                    if (columns.length >= maxColumns) {
                        return;
                    }
                    if (typeof item !== 'string') {
                        item = item === undefined || item === null ? '' : String(item);
                    }
                    var trimmed = item.trim();
                    columns.push(trimmed !== '' ? trimmed.slice(0, 120) : 'ستون بدون عنوان');
                });
            }
            if (Array.isArray(value.rows)) {
                value.rows.forEach(function (row) {
                    if (!Array.isArray(row) || rows.length >= maxRows) {
                        return;
                    }
                    rows.push(row.slice(0, maxColumns).map(function (cell) {
                        if (typeof cell !== 'string') {
                            cell = cell === undefined || cell === null ? '' : String(cell);
                        }
                        return cell.trim().slice(0, 240);
                    }));
                });
            }
        }

        if (columns.length === 0) {
            columns = ['ستون اول'];
        }

        var columnCount = columns.length;
        if (rows.length === 0) {
            rows.push(new Array(columnCount).fill(''));
        } else {
            rows = rows.map(function (row) {
                var normalizedRow = row.slice(0, columnCount);
                if (normalizedRow.length < columnCount) {
                    for (var i = normalizedRow.length; i < columnCount; i += 1) {
                        normalizedRow.push('');
                    }
                }
                return normalizedRow;
            }).slice(0, maxRows);
        }

        return {
            columns: columns,
            rows: rows
        };
    };

    var getElementPreviewContent = function (element, definition) {
        if (!element || !definition) {
            return '';
        }
        var props = element.props || {};
        switch (element.type) {
            case 'hero_heading':
                var baseText = props.text ? '«' + truncateText(props.text, 48) + '»' : (definition.description || '');
                var styleLabels = {
                    plain: 'ساده',
                    pill: 'پس‌زمینه گرد',
                    outline: 'قاب دور',
                    ribbon: 'روبان',
                    underline: 'خط برجسته'
                };
                var meta = [];
                
                // Show font size if available
                if (props.fontSize && typeof props.fontSize === 'number') {
                    meta.push(props.fontSize + 'px');
                } else if (props.variant) {
                    // Fallback to old variant system
                    var variantLabels = {
                        display: 'بسیار بزرگ',
                        headline: 'بزرگ',
                        title: 'متوسط'
                    };
                    var cleanVariant = typeof props.variant === 'string' ? props.variant.trim().toLowerCase() : '';
                    if (variantLabels[cleanVariant]) {
                        meta.push(variantLabels[cleanVariant]);
                    }
                }
                
                var cleanStyle = typeof props.style === 'string' ? props.style.trim().toLowerCase() : '';
                if (styleLabels[cleanStyle] && cleanStyle !== 'plain') {
                    meta.push(styleLabels[cleanStyle]);
                }
                if (meta.length > 0) {
                    baseText += ' (' + meta.join(' · ') + ')';
                }
                return baseText;
            case 'section_heading':
                return props.text ? '«' + truncateText(props.text, 48) + '»' : (definition.description || '');
            case 'custom_paragraph':
                return props.text ? truncateText(props.text, 96) : (definition.description || '');
            case 'user_full_name':
            case 'user_job_title':
                return props.showLabel ? (props.label || definition.description || '') : 'پیش‌نمایش اطلاعات کاربر';
            case 'user_profile_field':
                var fieldLabels = {
                    full_name: 'نام و نام خانوادگی',
                    national_id: 'کد ملی',
                    personnel_code: 'کد پرسنلی',
                    job_title: 'عنوان شغلی',
                    organization_post: 'پست سازمانی',
                    department: 'واحد سازمانی',
                    service_location: 'محل خدمت',
                    username: 'نام کاربری'
                };
                var profileField = props.field && fieldLabels[props.field] ? props.field : 'national_id';
                var label = props.customLabel && props.customLabel.trim() !== '' ? props.customLabel : fieldLabels[profileField];
                return props.showLabel ? 'نمایش «' + truncateText(label, 24) + '»' : 'اطلاعات: ' + fieldLabels[profileField];
            case 'user_profile_overview':
                if (props.title && props.title.trim() !== '') {
                    return '«' + truncateText(props.title, 32) + '»';
                }
                return 'کارت مشخصات کاربر';
            case 'custom_image':
                if (props.mode === 'static') {
                    return props.staticUrl
                        ? 'تصویر از ' + truncateText(props.staticUrl, 48)
                        : 'آدرس تصویر ثابت را وارد کنید';
                }
                var dynamicLabels = {
                    evaluation_cover: 'تصویر کاور ارزیابی',
                    organization_logo: 'لوگوی سازمان',
                    participant_avatar: 'تصویر ارزیاب‌شونده',
                    competency_model: 'تصویر مدل شایستگی'
                };
                var dynamicKey = typeof props.dynamicSource === 'string' ? props.dynamicSource : 'evaluation_cover';
                var dynamicLabel = dynamicLabels[dynamicKey] || 'تصویر پویا';
                return 'تصویر پویا: ' + dynamicLabel;
            case 'dynamic_table':
                var mode = props.mode || 'custom';
                var styleMap = {
                    grid: 'طرح شبکه‌ای',
                    minimal: 'طرح مینیمال',
                    striped: 'طرح راه‌راه',
                    soft: 'طرح کارتی'
                };
                var styleKey = typeof props.tableStyle === 'string' ? props.tableStyle.trim().toLowerCase() : '';
                var styleLabel = styleMap[styleKey] || '';
                var sizeBehaviorMap = {
                    auto_scale: 'تناسب با صفحه',
                    allow_split: 'تقسیم روی صفحات'
                };
                var sizeBehaviorKey = typeof props.sizeBehavior === 'string' ? props.sizeBehavior.trim().toLowerCase() : '';
                var sizeBehaviorLabel = sizeBehaviorMap[sizeBehaviorKey] || '';
                var makeDescriptor = function (baseText) {
                    var descriptors = [];
                    if (styleLabel) {
                        descriptors.push(styleLabel);
                    }
                    if (sizeBehaviorLabel) {
                        descriptors.push(sizeBehaviorLabel);
                    }
                    if (descriptors.length > 0) {
                        return baseText + ' - ' + descriptors.join(' | ');
                    }
                    return baseText;
                };
                if (mode === 'custom') {
                    var previewTable = normalizeTableData(props.tableData);
                    var sizeLabel = previewTable.rows.length + ' × ' + previewTable.columns.length;
                    return makeDescriptor('جدول سفارشی ' + sizeLabel);
                }
                if (mode === 'competency_model') {
                    return makeDescriptor('جدول شایستگی‌های ارزیابی');
                }
                if (mode === 'evaluation_tools') {
                    return makeDescriptor('جدول ابزارهای انتخاب‌شده');
                }
                return 'نمایش جدول داده';
            case 'assessment_tool_cards':
                var layoutMap = {
                    grid: 'چیدمان شبکه‌ای',
                    list: 'چیدمان فهرستی',
                    compact: 'چیدمان فشرده'
                };
                var layoutLabel = layoutMap[props.layout] || layoutMap.grid;
                var cardCount = props.maxItems || 3;
                var displayMode = typeof props.displayMode === 'string'
                    ? props.displayMode.trim().toLowerCase()
                    : 'all';
                if (['all', 'selected'].indexOf(displayMode) === -1) {
                    displayMode = 'all';
                }
                var selectedCount = 0;
                if (Array.isArray(props.selectedToolIds)) {
                    selectedCount = props.selectedToolIds.length;
                } else if (typeof props.selectedToolIds === 'string') {
                    var trimmedSelection = props.selectedToolIds.trim();
                    if (trimmedSelection !== '') {
                        try {
                            var parsedSelection = JSON.parse(trimmedSelection);
                            if (Array.isArray(parsedSelection)) {
                                selectedCount = parsedSelection.length;
                            } else {
                                selectedCount = trimmedSelection.split(',').filter(function (token) {
                                    return token.trim() !== '';
                                }).length;
                            }
                        } catch (error) {
                            selectedCount = trimmedSelection.split(',').filter(function (token) {
                                return token.trim() !== '';
                            }).length;
                        }
                    }
                }
                var descriptorParts = [layoutLabel];
                if (displayMode === 'selected') {
                    descriptorParts.push('انتخابی');
                    descriptorParts.push((selectedCount || 0) + ' مورد');
                } else {
                    descriptorParts.push('همه ابزارها');
                    descriptorParts.push('حداکثر ' + cardCount + ' مورد');
                }
                return 'کارت ابزارها (' + descriptorParts.join(' · ') + ')';
            case 'mbti_profile':
                var mbtiSample = datasets.mbtiProfileSample || {};
                var typeCode = mbtiSample.type_code || 'MBTI';
                var persona = mbtiSample.persona_name ? mbtiSample.persona_name : '';
                var descriptor = 'پروفایل ' + typeCode;
                if (persona) {
                    descriptor += ' - ' + truncateText(persona, 32);
                }
                var selectionInfo = '';
                var selectionValue = element.props ? element.props.selectedFeatureCategories : undefined;
                if (selectionValue !== undefined && selectionValue !== null) {
                    var selectionList;
                    if (Array.isArray(selectionValue)) {
                        selectionList = selectionValue;
                    } else if (typeof selectionValue === 'string' && selectionValue.trim() !== '') {
                        try {
                            var parsedSelection = JSON.parse(selectionValue);
                            selectionList = Array.isArray(parsedSelection) ? parsedSelection : selectionValue.split(',');
                        } catch (error) {
                            selectionList = selectionValue.split(',');
                        }
                    }
                    if (Array.isArray(selectionList)) {
                        var cleanSelection = selectionList.filter(function (item) {
                            return typeof item === 'string' && item.trim() !== '';
                        });
                        if (cleanSelection.length === 0) {
                            selectionInfo = 'بدون دسته';
                        } else {
                            selectionInfo = cleanSelection.length + ' دسته';
                        }
                    }
                }
                var metaParts = ['داده‌های آزمون'];
                if (element.props && normalizeBoolean(element.props.showTypeOverview) === false) {
                    metaParts.push('بدون تیتر تیپ');
                }
                if (selectionInfo) {
                    metaParts.push(selectionInfo);
                }
                if (element.props && normalizeBoolean(element.props.startOnNextPage)) {
                    metaParts.push('صفحه بعد');
                }
                    var labelOverrides = element.props ? element.props.featureCategoryLabels : undefined;
                    if (labelOverrides) {
                        var labelCount = 0;
                        if (Array.isArray(labelOverrides)) {
                            labelCount = labelOverrides.length;
                        } else if (typeof labelOverrides === 'object') {
                            labelCount = Object.keys(labelOverrides).filter(function (key) {
                                return key && key.trim() !== '' && labelOverrides[key] && String(labelOverrides[key]).trim() !== '';
                            }).length;
                        } else if (typeof labelOverrides === 'string') {
                            try {
                                var parsedLabels = JSON.parse(labelOverrides);
                                if (parsedLabels && typeof parsedLabels === 'object') {
                                    labelCount = Object.keys(parsedLabels).length;
                                }
                            } catch (error) {
                                labelCount = 0;
                            }
                        }
                        if (labelCount > 0) {
                            metaParts.push(labelCount + ' عنوان فارسی');
                        }
                    }
                    return descriptor + ' (' + metaParts.join(' · ') + ')';
            case 'mbti_type_matrix':
                var matrixSample = datasets.mbtiProfileSample || {};
                var matrixTypeCode = matrixSample.type_code || 'MBTI';
                var normalizedMatrixType = '';
                if (typeof matrixTypeCode === 'string') {
                    normalizedMatrixType = matrixTypeCode.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 4);
                }
                if (!normalizedMatrixType) {
                    normalizedMatrixType = 'MBTI';
                }
                var matrixMeta = [];
                if (element.props && normalizeBoolean(element.props.showLegend)) {
                    matrixMeta.push('توضیح فعال');
                }
                if (element.props && normalizeBoolean(element.props.startOnNextPage)) {
                    matrixMeta.push('صفحه بعد');
                }
                var matrixDescriptor = 'ماتریس MBTI ' + normalizedMatrixType;
                if (matrixMeta.length > 0) {
                    matrixDescriptor += ' (' + matrixMeta.join(' · ') + ')';
                }
                return matrixDescriptor;
            case 'disc_profile_chart':
                var dominantScores = [props.scoreD, props.scoreI, props.scoreS, props.scoreC]
                    .map(function (value) { return Number(value) || 0; });
                var maxScore = Math.max.apply(null, dominantScores);
                var dominantLabel = 'DISC';
                if (maxScore > 0) {
                    var letters = ['D', 'I', 'S', 'C'];
                    dominantLabel = letters[dominantScores.indexOf(maxScore)] || 'DISC';
                }
                return 'نمودار DISC (غالب: ' + dominantLabel + ')';
            case 'gauge_indicator':
                var gaugeLabel = props.label || 'شاخص';
                var currentValue = Number(props.value) || 0;
                var maxValueGauge = Number(props.maxValue) || 100;
                var percent = maxValueGauge > 0 ? Math.round((currentValue / maxValueGauge) * 100) : 0;
                percent = Math.max(0, Math.min(100, percent));
                return 'گیج ' + truncateText(gaugeLabel, 28) + ' (' + percent + '%)';
            case 'washup_agreed_competencies':
                var layoutValue = props.layout && typeof props.layout === 'string'
                    ? props.layout.trim().toLowerCase()
                    : 'cards';
                var layoutLabelMap = {
                    cards: 'چیدمان کارتی',
                    list: 'چیدمان فهرستی',
                    table: 'نمایش جدولی'
                };
                var layoutLabel = layoutLabelMap[layoutValue] || layoutLabelMap.cards;
                var washupDataset = datasets.washupAgreedSample || {};
                var availableCount = 0;
                if (Array.isArray(washupDataset.items)) {
                    availableCount = washupDataset.items.length;
                } else if (typeof washupDataset.items_count === 'number') {
                    availableCount = washupDataset.items_count;
                }
                var maxItems = parseInt(props.maxItems, 10);
                if (isNaN(maxItems) || maxItems <= 0) {
                    maxItems = availableCount || 6;
                }
                maxItems = Math.max(1, maxItems);
                var effectiveCount = availableCount > 0 ? Math.min(availableCount, maxItems) : maxItems;
                var countLabel = availableCount > 0
                    ? effectiveCount + ' مورد'
                    : 'حداکثر ' + effectiveCount + ' مورد';
                return 'شایستگی‌های Wash-Up (' + layoutLabel + ' · ' + countLabel + ')';
            case 'logo_display':
                var baseLogoLabel = props.source === 'system' ? 'لوگوی سامانه' : 'لوگوی سازمان';
                var percentVal = Number(props.sizePercent) || 0;
                if (percentVal > 0) {
                    var clampedPercent = Math.max(5, Math.min(100, Math.floor(percentVal)));
                    return baseLogoLabel + ' - ' + clampedPercent + '%';
                }
                return baseLogoLabel;
            case 'signature_block':
                return 'سطری برای امضا و مهر سازمان';
            case 'score_badges':
                return 'نشان‌های امتیاز سازماندهی شده';
            case 'chart_placeholder':
                return 'محل درج نمودار ' + (props.chartType || 'radar');
            default:
                return definition.description || '';
        }
    };

    var initialState = parseJsonSafe(root.dataset.initialState, null);
    var componentLibrary = parseJsonSafe(root.dataset.componentLibrary, []);
    var templateOptions = parseJsonSafe(root.dataset.templateOptions, []);
    var datasets = parseJsonSafe(root.dataset.datasets, {});

    var componentMap = {};
    componentLibrary.forEach(function (component) {
        componentMap[component.type] = component;
    });

    var getMbtiFeatureCategories = function () {
        var source = datasets.mbtiProfileSample || {};
        var rawGroups = Array.isArray(source.feature_groups) ? source.feature_groups : [];
        var categories = [];
        rawGroups.forEach(function (group) {
            if (!group) {
                return;
            }
            var title = '';
            if (typeof group.category === 'string' && group.category.trim() !== '') {
                title = group.category.trim();
            } else if (typeof group.title === 'string' && group.title.trim() !== '') {
                title = group.title.trim();
            }
            if (title === '') {
                return;
            }
            if (categories.indexOf(title) === -1) {
                categories.push(title);
            }
        });
        return categories;
    };

    var showElementSettingsModal = function () {
        if (!elementSettingsModal) {
            return;
        }
        if (window.bootstrap && window.bootstrap.Modal) {
            if (!elementSettingsModalInstance) {
                elementSettingsModalInstance = new window.bootstrap.Modal(elementSettingsModal, {
                    backdrop: 'static',
                    keyboard: true
                });
            }
            elementSettingsModalInstance.show();
        } else {
            isModalFallback = true;
            elementSettingsModal.classList.add('show');
            elementSettingsModal.style.display = 'block';
            document.body.classList.add('modal-open');
            var scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
            if (scrollbarWidth > 0) {
                document.body.style.paddingRight = scrollbarWidth + 'px';
            }
        }
    };

    var hideElementSettingsModal = function () {
        if (!elementSettingsModal) {
            return;
        }
        if (window.bootstrap && window.bootstrap.Modal && elementSettingsModalInstance) {
            elementSettingsModalInstance.hide();
        } else if (isModalFallback) {
            elementSettingsModal.classList.remove('show');
            elementSettingsModal.style.display = 'none';
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '';
            isModalFallback = false;
        }
    };

    var defaultTemplateKey = templateOptions.length > 0 ? templateOptions[0].key : 'classic';
    var allowedPageSizes = ['a4', 'a5'];
    var defaultPageSize = 'a4';
    var pageSizeLabels = {
        a4: 'A4',
        a5: 'A5'
    };
    var allowedOrientations = ['portrait', 'landscape'];
    var defaultPageOrientation = 'portrait';
    var orientationLabels = {
        portrait: 'عمودی',
        landscape: 'افقی'
    };

    var builderState = initialState && typeof initialState === 'object' ? initialState : {
        version: 1,
        activePageId: null,
        pages: []
    };
    var selectedElementId = null;

    var ensureStateIntegrity = function () {
        if (!Array.isArray(builderState.pages)) {
            builderState.pages = [];
        }
        if (builderState.pages.length === 0) {
            var newPage = {
                id: generateId('page'),
                name: 'صفحه ۱',
                template: defaultTemplateKey,
                size: defaultPageSize,
                orientation: defaultPageOrientation,
                elements: []
            };
            builderState.pages.push(newPage);
            builderState.activePageId = newPage.id;
        }

        builderState.pages.forEach(function (page) {
            if (!page || typeof page !== 'object') {
                return;
            }
            if (typeof page.template !== 'string' || page.template === '') {
                page.template = defaultTemplateKey;
            }
            if (typeof page.size === 'string') {
                page.size = page.size.toLowerCase();
            }
            if (typeof page.size !== 'string' || allowedPageSizes.indexOf(page.size) === -1) {
                page.size = defaultPageSize;
            }
            if (typeof page.orientation === 'string') {
                page.orientation = page.orientation.toLowerCase();
            }
            if (typeof page.orientation !== 'string' || allowedOrientations.indexOf(page.orientation) === -1) {
                page.orientation = defaultPageOrientation;
            }
            if (!Array.isArray(page.elements)) {
                page.elements = [];
            }
            page.elements = page.elements.map(function (element) {
                if (!element || typeof element !== 'object') {
                    return element;
                }
                if (!element.props || typeof element.props !== 'object') {
                    element.props = {};
                }
                var definition = componentMap[element.type];
                if (definition && definition.defaultProps) {
                    var merged = {};
                    Object.keys(definition.defaultProps).forEach(function (propKey) {
                        merged[propKey] = definition.defaultProps[propKey];
                    });
                    Object.keys(element.props).forEach(function (propKey) {
                        merged[propKey] = element.props[propKey];
                    });
                    element.props = merged;
                    if (element.type === 'logo_display' && !element.props.widthMode && element.props.layout) {
                        element.props.widthMode = element.props.layout;
                    }
                }
                if (!Object.prototype.hasOwnProperty.call(element.props, 'applyToAllPages')) {
                    element.props.applyToAllPages = 0;
                } else {
                    element.props.applyToAllPages = normalizeBoolean(element.props.applyToAllPages) ? 1 : 0;
                }
                if (element.type === 'dynamic_table') {
                    element.props.tableData = normalizeTableData(element.props.tableData);
                    var allowedTableStyles = ['grid', 'minimal', 'striped', 'soft'];
                    var tableStyle = typeof element.props.tableStyle === 'string'
                        ? element.props.tableStyle.trim().toLowerCase()
                        : '';
                    if (allowedTableStyles.indexOf(tableStyle) === -1) {
                        element.props.tableStyle = 'grid';
                    } else {
                        element.props.tableStyle = tableStyle;
                    }
                    var allowedSizeBehaviors = ['auto_scale', 'allow_split'];
                    var sizeBehavior = typeof element.props.sizeBehavior === 'string'
                        ? element.props.sizeBehavior.trim().toLowerCase()
                        : '';
                    if (allowedSizeBehaviors.indexOf(sizeBehavior) === -1) {
                        element.props.sizeBehavior = 'auto_scale';
                    } else {
                        element.props.sizeBehavior = sizeBehavior;
                    }
                }
                if (element.type === 'assessment_tool_cards') {
                    var displayMode = typeof element.props.displayMode === 'string'
                        ? element.props.displayMode.trim().toLowerCase()
                        : 'all';
                    if (['all', 'selected'].indexOf(displayMode) === -1) {
                        element.props.displayMode = 'all';
                    } else {
                        element.props.displayMode = displayMode;
                    }

                    var normalizeSelected = function (input) {
                        var values = [];
                        if (Array.isArray(input)) {
                            input.forEach(function (item) {
                                if (item && typeof item === 'object') {
                                    if (Object.prototype.hasOwnProperty.call(item, 'value')) {
                                        item = item.value;
                                    } else if (Object.prototype.hasOwnProperty.call(item, 'id')) {
                                        item = item.id;
                                    }
                                }
                                if (item === undefined || item === null) {
                                    return;
                                }
                                var stringValue = String(item).trim();
                                if (stringValue === '') {
                                    return;
                                }
                                if (values.indexOf(stringValue) === -1) {
                                    values.push(stringValue);
                                }
                            });
                        } else if (typeof input === 'string') {
                            var trimmed = input.trim();
                            if (trimmed !== '') {
                                try {
                                    var parsed = JSON.parse(trimmed);
                                    if (Array.isArray(parsed)) {
                                        return normalizeSelected(parsed);
                                    }
                                } catch (error) {
                                    // Ignore JSON parse errors; fall back to comma split.
                                }
                                trimmed.split(',').forEach(function (piece) {
                                    var value = piece.trim();
                                    if (value !== '' && values.indexOf(value) === -1) {
                                        values.push(value);
                                    }
                                });
                            }
                        }
                        return values;
                    };

                    var normalizedSelection = normalizeSelected(element.props.selectedToolIds);
                    var selectionLimit = 20;
                    if (normalizedSelection.length > selectionLimit) {
                        normalizedSelection = normalizedSelection.slice(0, selectionLimit);
                    }
                    element.props.selectedToolIds = normalizedSelection;
                }
                return element;
            });
        });
        var hasActive = builderState.pages.some(function (page) {
            return page.id === builderState.activePageId;
        });
        if (!hasActive) {
            builderState.activePageId = builderState.pages[0].id;
        }
    };

    ensureStateIntegrity();

    var getActivePage = function () {
        return builderState.pages.find(function (page) {
            return page.id === builderState.activePageId;
        }) || builderState.pages[0];
    };

    var updateStateInput = function () {
        if (!stateInput) {
            return;
        }
        stateInput.value = JSON.stringify(builderState);
    };

    var addPage = function () {
        var pageNumber = builderState.pages.length + 1;
        var newPage = {
            id: generateId('page'),
            name: 'صفحه ' + pageNumber,
            template: defaultTemplateKey,
            size: defaultPageSize,
            orientation: defaultPageOrientation,
            elements: []
        };
        builderState.pages.push(newPage);
        builderState.activePageId = newPage.id;
        selectedElementId = null;
        renderAll();
    };

    var renamePage = function (pageId) {
        var page = builderState.pages.find(function (item) {
            return item.id === pageId;
        });
        if (!page) {
            return;
        }
        var nextName = window.prompt('نام صفحه را وارد کنید:', page.name);
        if (nextName === null) {
            return;
        }
        nextName = nextName.trim();
        if (nextName === '') {
            return;
        }
        page.name = nextName;
        renderAll();
    };

    var duplicatePage = function (pageId) {
        var page = builderState.pages.find(function (item) {
            return item.id === pageId;
        });
        if (!page) {
            return;
        }
        var clonedElements = page.elements.map(function (element) {
            return {
                id: generateId('el'),
                type: element.type,
                props: JSON.parse(JSON.stringify(element.props || {}))
            };
        });
        var duplicateSize = typeof page.size === 'string' ? page.size.toLowerCase() : '';
        var duplicateOrientation = typeof page.orientation === 'string' ? page.orientation.toLowerCase() : '';
        var duplicate = {
            id: generateId('page'),
            name: 'کپی از ' + page.name,
            template: page.template,
            size: allowedPageSizes.indexOf(duplicateSize) !== -1 ? duplicateSize : defaultPageSize,
            orientation: allowedOrientations.indexOf(duplicateOrientation) !== -1 ? duplicateOrientation : defaultPageOrientation,
            elements: clonedElements
        };
        builderState.pages.push(duplicate);
        builderState.activePageId = duplicate.id;
        selectedElementId = null;
        renderAll();
    };

    var deletePage = function (pageId) {
        if (builderState.pages.length <= 1) {
            window.alert('حداقل یک صفحه باید وجود داشته باشد.');
            return;
        }
        var confirmed = window.confirm('آیا از حذف این صفحه مطمئن هستید؟');
        if (!confirmed) {
            return;
        }
        builderState.pages = builderState.pages.filter(function (page) {
            return page.id !== pageId;
        });
        if (!builderState.pages.some(function (page) { return page.id === builderState.activePageId; })) {
            builderState.activePageId = builderState.pages[0].id;
        }
        selectedElementId = null;
        renderAll();
    };

    var createElementFromLibrary = function (type) {
        var definition = componentMap[type];
        if (!definition) {
            return null;
        }
        var props = JSON.parse(JSON.stringify(definition.defaultProps || {}));
        if (!Object.prototype.hasOwnProperty.call(props, 'applyToAllPages')) {
            props.applyToAllPages = 0;
        } else {
            props.applyToAllPages = normalizeBoolean(props.applyToAllPages) ? 1 : 0;
        }
        if (Object.prototype.hasOwnProperty.call(props, 'tableData')) {
            props.tableData = normalizeTableData(props.tableData);
        }
        return {
            id: generateId('el'),
            type: type,
            props: props
        };
    };

    var addElementToActivePage = function (type) {
        var page = getActivePage();
        if (!page) {
            return;
        }
        var element = createElementFromLibrary(type);
        if (!element) {
            return;
        }
        page.elements.push(element);
        selectedElementId = element.id;
        renderAll();
    };

    var findElementById = function (page, elementId) {
        return page.elements.find(function (element) {
            return element.id === elementId;
        }) || null;
    };

    var selectElement = function (elementId) {
        selectedElementId = elementId;
        renderCanvas();
        renderElementSettings();
        updateStateInput();
    };

    var removeElement = function (elementId) {
        var page = getActivePage();
        if (!page) {
            return;
        }
        page.elements = page.elements.filter(function (element) {
            return element.id !== elementId;
        });
        if (selectedElementId === elementId) {
            selectedElementId = null;
        }
        renderCanvas();
        renderElementSettings();
        updateStateInput();
    };

    var moveElement = function (elementId, direction) {
        var page = getActivePage();
        if (!page) {
            return;
        }
        var index = page.elements.findIndex(function (element) {
            return element.id === elementId;
        });
        if (index === -1) {
            return;
        }
        var targetIndex = index + direction;
        if (targetIndex < 0 || targetIndex >= page.elements.length) {
            return;
        }
        var temp = page.elements[index];
        page.elements[index] = page.elements[targetIndex];
        page.elements[targetIndex] = temp;
        renderCanvas();
        renderElementSettings();
        updateStateInput();
    };

    var duplicateElement = function (elementId) {
        var page = getActivePage();
        if (!page) {
            return;
        }
        var element = findElementById(page, elementId);
        if (!element) {
            return;
        }
        var cloned = {
            id: generateId('el'),
            type: element.type,
            props: JSON.parse(JSON.stringify(element.props || {}))
        };
        page.elements.splice(page.elements.indexOf(element) + 1, 0, cloned);
        selectedElementId = cloned.id;
        renderCanvas();
        renderElementSettings();
        updateStateInput();
    };

    var openElementSettings = function (elementId) {
        if (!elementId) {
            return;
        }
        var page = getActivePage();
        if (!page) {
            return;
        }
        if (selectedElementId !== elementId) {
            selectElement(elementId);
        } else {
            renderElementSettings();
        }
        showElementSettingsModal();
    };

    var renderPageList = function () {
        if (!pageListEl) {
            return;
        }
        pageListEl.innerHTML = '';
        builderState.pages.forEach(function (page, index) {
            var item = document.createElement('div');
            item.className = 'builder-page-item' + (page.id === builderState.activePageId ? ' active' : '');

            var title = document.createElement('div');
            title.className = 'builder-page-title';
            title.innerHTML = escapeHtml(page.name);
            item.appendChild(title);

            var meta = document.createElement('div');
            meta.className = 'text-muted small';
            var sizeLabel = pageSizeLabels[page.size] || (page.size ? page.size.toUpperCase() : 'A4');
            var orientationLabel = orientationLabels[page.orientation] || (page.orientation === 'landscape' ? 'افقی' : 'عمودی');
            meta.innerHTML = 'قالب: ' + escapeHtml(page.template || defaultTemplateKey) + ' · ' + escapeHtml(sizeLabel + ' / ' + orientationLabel) + ' · ' + (page.elements.length) + ' المان';
            item.appendChild(meta);

            var actions = document.createElement('div');
            actions.className = 'builder-page-actions';

            var renameBtn = document.createElement('button');
            renameBtn.type = 'button';
            renameBtn.className = 'btn btn-sm btn-outline-secondary';
            renameBtn.textContent = 'تغییر نام';
            renameBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                renamePage(page.id);
            });

            var duplicateBtn = document.createElement('button');
            duplicateBtn.type = 'button';
            duplicateBtn.className = 'btn btn-sm btn-outline-secondary';
            duplicateBtn.textContent = 'تکرار';
            duplicateBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                duplicatePage(page.id);
            });

            var deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'btn btn-sm btn-outline-danger';
            deleteBtn.textContent = 'حذف';
            deleteBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                deletePage(page.id);
            });

            actions.appendChild(renameBtn);
            actions.appendChild(duplicateBtn);
            actions.appendChild(deleteBtn);
            item.appendChild(actions);

            item.addEventListener('click', function () {
                builderState.activePageId = page.id;
                selectedElementId = null;
                renderAll();
            });

            pageListEl.appendChild(item);
        });
    };

    var renderComponentPalette = function () {
        if (!componentListEl) {
            return;
        }
        componentListEl.innerHTML = '';

        var grouped = {};
        componentLibrary.forEach(function (component) {
            var category = component.category || 'سایر';
            if (!grouped[category]) {
                grouped[category] = [];
            }
            grouped[category].push(component);
        });

        Object.keys(grouped).forEach(function (category) {
            var header = document.createElement('div');
            header.className = 'd-flex justify-content-between align-items-center mb-2 mt-1';

            var title = document.createElement('h6');
            title.className = 'mb-0 text-gray-600';
            title.textContent = category;
            header.appendChild(title);

            componentListEl.appendChild(header);

            grouped[category].forEach(function (component) {
                var item = document.createElement('div');
                item.className = 'builder-component-item';
                item.draggable = true;
                item.setAttribute('data-component-type', component.type);

                var icon = document.createElement('div');
                icon.innerHTML = '<ion-icon name="' + escapeHtml(component.icon || 'extension-puzzle-outline') + '"></ion-icon>';

                var info = document.createElement('div');
                info.className = 'builder-component-info';

                var nameEl = document.createElement('div');
                nameEl.className = 'fw-semibold';
                nameEl.textContent = component.title;

                var descEl = document.createElement('div');
                descEl.className = 'text-muted small';
                descEl.textContent = component.description || '';

                info.appendChild(nameEl);
                info.appendChild(descEl);

                item.appendChild(icon);
                item.appendChild(info);

                item.addEventListener('dragstart', function (event) {
                    event.dataTransfer.setData('component-type', component.type);
                    event.dataTransfer.effectAllowed = 'copy';
                });

                item.addEventListener('dblclick', function () {
                    addElementToActivePage(component.type);
                });

                componentListEl.appendChild(item);
            });
        });
    };

    var renderPageHeader = function (page) {
        if (!pageMetaEl || !pageTemplateEl) {
            return;
        }

        pageMetaEl.innerHTML = '';
        pageTemplateEl.innerHTML = '';

        var metaContainer = document.createElement('div');
        metaContainer.className = 'builder-page-meta';

        var title = document.createElement('div');
        title.className = 'builder-page-title';
        title.innerHTML = escapeHtml(page.name);

        var tag = document.createElement('span');
        tag.className = 'builder-tag';
        var sizeLabel = pageSizeLabels[page.size] || (page.size ? page.size.toUpperCase() : 'A4');
        var orientationLabel = orientationLabels[page.orientation] || (page.orientation === 'landscape' ? 'افقی' : 'عمودی');
        tag.innerHTML = '<ion-icon name="layers-outline"></ion-icon>' + escapeHtml(page.elements.length + ' المان · ' + sizeLabel + ' / ' + orientationLabel);

        metaContainer.appendChild(title);
        metaContainer.appendChild(tag);

        pageMetaEl.appendChild(metaContainer);

        var templateContainer = document.createElement('div');
        templateContainer.className = 'builder-template-select';

        var label = document.createElement('label');
        label.className = 'form-label mb-0';
        label.textContent = 'قالب صفحه';

        var select = document.createElement('select');
        select.className = 'form-select form-select-sm w-auto';
        templateOptions.forEach(function (option) {
            var opt = document.createElement('option');
            opt.value = option.key;
            opt.textContent = option.name;
            if (option.key === page.template) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });
        select.addEventListener('change', function () {
            page.template = select.value;
            renderPageList();
            updateStateInput();
        });

        templateContainer.appendChild(label);
        templateContainer.appendChild(select);
        pageTemplateEl.appendChild(templateContainer);

        var layoutContainer = document.createElement('div');
        layoutContainer.className = 'builder-template-select d-flex flex-wrap align-items-center gap-12 mt-2';

        var sizeGroup = document.createElement('div');
        sizeGroup.className = 'd-flex align-items-center gap-8';

        var sizeLabel = document.createElement('label');
        sizeLabel.className = 'form-label mb-0 small text-muted';
        sizeLabel.textContent = 'اندازه صفحه';

        var sizeSelect = document.createElement('select');
        sizeSelect.className = 'form-select form-select-sm w-auto';
        allowedPageSizes.forEach(function (sizeKey) {
            var opt = document.createElement('option');
            opt.value = sizeKey;
            opt.textContent = pageSizeLabels[sizeKey] || sizeKey.toUpperCase();
            if (sizeKey === page.size) {
                opt.selected = true;
            }
            sizeSelect.appendChild(opt);
        });
        sizeSelect.addEventListener('change', function () {
            page.size = sizeSelect.value;
            renderAll();
        });

        sizeGroup.appendChild(sizeLabel);
        sizeGroup.appendChild(sizeSelect);
        layoutContainer.appendChild(sizeGroup);

        var orientationGroup = document.createElement('div');
        orientationGroup.className = 'd-flex align-items-center gap-8';

        var orientationLabelEl = document.createElement('label');
        orientationLabelEl.className = 'form-label mb-0 small text-muted';
        orientationLabelEl.textContent = 'چیدمان';

        var orientationSelect = document.createElement('select');
        orientationSelect.className = 'form-select form-select-sm w-auto';
        allowedOrientations.forEach(function (orientationKey) {
            var opt = document.createElement('option');
            opt.value = orientationKey;
            opt.textContent = orientationLabels[orientationKey] || orientationKey;
            if (orientationKey === page.orientation) {
                opt.selected = true;
            }
            orientationSelect.appendChild(opt);
        });
        orientationSelect.addEventListener('change', function () {
            page.orientation = orientationSelect.value;
            renderAll();
        });

        orientationGroup.appendChild(orientationLabelEl);
        orientationGroup.appendChild(orientationSelect);
        layoutContainer.appendChild(orientationGroup);

        pageTemplateEl.appendChild(layoutContainer);
    };

    var renderPageLayoutControls = function () {
        if (!pageLayoutControlsEl) {
            return;
        }

        pageLayoutControlsEl.innerHTML = '';

        var page = getActivePage();
        if (!page) {
            var emptyMessage = document.createElement('div');
            emptyMessage.className = 'text-muted small';
            emptyMessage.textContent = 'صفحه‌ای انتخاب نشده است.';
            pageLayoutControlsEl.appendChild(emptyMessage);
            return;
        }

        var title = document.createElement('div');
        title.className = 'builder-page-layout-controls-title';
        title.textContent = 'چیدمان صفحه فعال';
        pageLayoutControlsEl.appendChild(title);

        var helper = document.createElement('div');
        helper.className = 'text-muted small';
        helper.textContent = 'اندازه و جهت صفحه انتخاب‌شده را تعیین کنید.';
        pageLayoutControlsEl.appendChild(helper);

        var sizeGroup = document.createElement('div');
        sizeGroup.className = 'builder-control-group';

        var sizeLabelEl = document.createElement('label');
        sizeLabelEl.className = 'form-label';
        sizeLabelEl.textContent = 'اندازه صفحه';
        sizeGroup.appendChild(sizeLabelEl);

        var sizeSelect = document.createElement('select');
        sizeSelect.className = 'form-select form-select-sm';
        allowedPageSizes.forEach(function (sizeKey) {
            var opt = document.createElement('option');
            opt.value = sizeKey;
            opt.textContent = pageSizeLabels[sizeKey] || sizeKey.toUpperCase();
            if (sizeKey === page.size) {
                opt.selected = true;
            }
            sizeSelect.appendChild(opt);
        });
        sizeSelect.addEventListener('change', function () {
            page.size = sizeSelect.value;
            renderAll();
        });
        sizeGroup.appendChild(sizeSelect);
        pageLayoutControlsEl.appendChild(sizeGroup);

        var orientationGroup = document.createElement('div');
        orientationGroup.className = 'builder-control-group';

        var orientationLabel = document.createElement('label');
        orientationLabel.className = 'form-label';
        orientationLabel.textContent = 'جهت صفحه';
        orientationGroup.appendChild(orientationLabel);

        var orientationSelect = document.createElement('select');
        orientationSelect.className = 'form-select form-select-sm';
        allowedOrientations.forEach(function (orientationKey) {
            var opt = document.createElement('option');
            opt.value = orientationKey;
            opt.textContent = orientationLabels[orientationKey] || orientationKey;
            if (orientationKey === page.orientation) {
                opt.selected = true;
            }
            orientationSelect.appendChild(opt);
        });
        orientationSelect.addEventListener('change', function () {
            page.orientation = orientationSelect.value;
            renderAll();
        });
        orientationGroup.appendChild(orientationSelect);
        pageLayoutControlsEl.appendChild(orientationGroup);
    };

    var renderCanvas = function () {
        var page = getActivePage();
        if (!page) {
            return;
        }

        renderPageHeader(page);

        if (dropPlaceholderEl) {
            dropPlaceholderEl.style.display = page.elements.length === 0 ? 'block' : 'none';
        }

        if (!canvasElementsEl) {
            return;
        }
        canvasElementsEl.innerHTML = '';

        page.elements.forEach(function (element, index) {
            var definition = componentMap[element.type] || {};
            var card = document.createElement('div');
            card.className = 'builder-element-card' + (element.id === selectedElementId ? ' selected' : '');
            card.setAttribute('data-element-id', element.id);

            var header = document.createElement('div');
            header.className = 'builder-element-header';

            var title = document.createElement('div');
            title.className = 'fw-semibold d-flex align-items-center gap-8';
            title.innerHTML = '<ion-icon name="' + escapeHtml(definition.icon || 'extension-puzzle-outline') + '"></ion-icon>' + escapeHtml(definition.title || element.type);
            if (element.props && normalizeBoolean(element.props.applyToAllPages)) {
                var globalBadge = document.createElement('span');
                globalBadge.className = 'badge builder-element-global-badge';
                globalBadge.textContent = 'تمام صفحات';
                title.appendChild(globalBadge);
            }

            var actions = document.createElement('div');
            actions.className = 'builder-element-actions';

            var settingsBtn = document.createElement('button');
            settingsBtn.type = 'button';
            settingsBtn.className = 'btn btn-sm btn-outline-secondary';
            settingsBtn.innerHTML = '<ion-icon name="settings-outline"></ion-icon>';
            settingsBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                openElementSettings(element.id);
            });

            var upBtn = document.createElement('button');
            upBtn.type = 'button';
            upBtn.className = 'btn btn-sm btn-light';
            upBtn.innerHTML = '<ion-icon name="chevron-up-outline"></ion-icon>';
            upBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                moveElement(element.id, -1);
            });
            if (index === 0) {
                upBtn.disabled = true;
            }

            var downBtn = document.createElement('button');
            downBtn.type = 'button';
            downBtn.className = 'btn btn-sm btn-light';
            downBtn.innerHTML = '<ion-icon name="chevron-down-outline"></ion-icon>';
            downBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                moveElement(element.id, 1);
            });
            if (index === page.elements.length - 1) {
                downBtn.disabled = true;
            }

            var duplicateBtn = document.createElement('button');
            duplicateBtn.type = 'button';
            duplicateBtn.className = 'btn btn-sm btn-light';
            duplicateBtn.innerHTML = '<ion-icon name="copy-outline"></ion-icon>';
            duplicateBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                duplicateElement(element.id);
            });

            var deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'btn btn-sm btn-outline-danger';
            deleteBtn.innerHTML = '<ion-icon name="trash-outline"></ion-icon>';
            deleteBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                removeElement(element.id);
            });

            actions.appendChild(upBtn);
            actions.appendChild(downBtn);
            actions.appendChild(duplicateBtn);
            actions.appendChild(deleteBtn);
            actions.insertBefore(settingsBtn, upBtn);

            header.appendChild(title);
            header.appendChild(actions);

            var preview = document.createElement('div');
            preview.className = 'builder-element-preview';
            preview.textContent = getElementPreviewContent(element, definition);

            card.appendChild(header);
            card.appendChild(preview);

            card.addEventListener('click', function () {
                selectElement(element.id);
            });

            card.addEventListener('dblclick', function (event) {
                event.stopPropagation();
                openElementSettings(element.id);
            });

            canvasElementsEl.appendChild(card);
        });
    };

    var renderElementSettings = function () {
        if (!elementSettingsModalBody) {
            return;
        }

        elementSettingsModalBody.innerHTML = '';
        if (elementSettingsModalTitle) {
            elementSettingsModalTitle.textContent = 'تنظیمات آیتم';
        }
        var page = getActivePage();
        if (!page) {
            return;
        }

        var element = selectedElementId ? findElementById(page, selectedElementId) : null;
        if (!element) {
            var emptyState = document.createElement('div');
            emptyState.className = 'builder-settings-empty';
            emptyState.textContent = 'برای ویرایش تنظیمات، یک عنصر را در صفحه انتخاب کنید.';
            elementSettingsModalBody.appendChild(emptyState);
            return;
        }

        var definition = componentMap[element.type] || null;
        if (!definition) {
            return;
        }

        if (elementSettingsModalTitle) {
            elementSettingsModalTitle.textContent = definition.title || element.type;
        }

        if (!element.props || typeof element.props !== 'object') {
            element.props = {};
        }

        var defaultProps = definition.defaultProps || {};
        var getPropValue = function (propKey) {
            if (Object.prototype.hasOwnProperty.call(element.props, propKey)) {
                return element.props[propKey];
            }
            return Object.prototype.hasOwnProperty.call(defaultProps, propKey) ? defaultProps[propKey] : undefined;
        };
        var isDependencySatisfied = function (field) {
            if (!field || !field.dependsOn) {
                return true;
            }
            return Object.keys(field.dependsOn).every(function (dependencyKey) {
                var expected = field.dependsOn[dependencyKey];
                var current = getPropValue(dependencyKey);
                if (typeof expected === 'boolean') {
                    return normalizeBoolean(current) === expected;
                }
                if (typeof expected === 'number') {
                    return Number(current) === expected;
                }
                return String(current) === String(expected);
            });
        };

        var form = document.createElement('div');
        form.className = 'builder-settings-form';

        if (definition.description) {
            var description = document.createElement('div');
            description.className = 'text-muted small mb-3';
            description.textContent = definition.description;
            form.appendChild(description);
        }

        var fields = Array.isArray(definition.configFields) ? definition.configFields : [];
        var fieldMetadata = [];

        var resolveFieldOptions = function (fieldDefinition) {
            if (!fieldDefinition) {
                return [];
            }
            if (Array.isArray(fieldDefinition.options) && fieldDefinition.options.length > 0) {
                return fieldDefinition.options;
            }
            if (fieldDefinition.optionsKey && datasets && Object.prototype.hasOwnProperty.call(datasets, fieldDefinition.optionsKey)) {
                var datasetItems = datasets[fieldDefinition.optionsKey];
                if (Array.isArray(datasetItems)) {
                    return datasetItems;
                }
            }
            return [];
        };

        var refreshFieldVisibility = function () {
            fieldMetadata.forEach(function (meta) {
                var visible = isDependencySatisfied(meta.field);
                if (visible) {
                    meta.wrapper.classList.remove('d-none');
                } else {
                    meta.wrapper.classList.add('d-none');
                }
            });
        };

        fields.forEach(function (field) {
            if (!field || !field.key) {
                return;
            }

            var key = field.key;
            var wrapper = document.createElement('div');
            wrapper.className = 'mb-3 builder-settings-field';

            if (!isDependencySatisfied(field)) {
                wrapper.classList.add('d-none');
            }

            var label = document.createElement('label');
            label.className = 'form-label fw-semibold';
            label.textContent = field.label || key;
            wrapper.appendChild(label);

            var helpText = null;
            if (field.help) {
                helpText = document.createElement('div');
                helpText.className = 'form-text text-muted';
                helpText.textContent = field.help;
            }

            var currentValue = getPropValue(key);
            var controlContainer = document.createElement('div');
            controlContainer.className = 'builder-settings-control';
            var control;

            var handleUpdate = function () {
                updateStateInput();
                renderCanvas();
                refreshFieldVisibility();
            };

            switch (field.type) {
                case 'note':
                    control = document.createElement('div');
                    control.className = 'alert alert-info builder-settings-note';
                    control.textContent = field.note || '';
                    controlContainer.appendChild(control);
                    break;
                case 'toggle':
                    control = document.createElement('div');
                    control.className = 'form-check form-switch';
                    var inputToggle = document.createElement('input');
                    inputToggle.type = 'checkbox';
                    inputToggle.className = 'form-check-input';
                    inputToggle.checked = normalizeBoolean(currentValue);
                    inputToggle.addEventListener('change', function () {
                        element.props[key] = inputToggle.checked ? 1 : 0;
                        handleUpdate();
                    });
                    control.appendChild(inputToggle);
                    controlContainer.appendChild(control);
                    break;
                case 'select':
                    control = document.createElement('select');
                    control.className = 'form-select';
                    var selectOptions = resolveFieldOptions(field);
                    if (selectOptions.length === 0) {
                        var fallbackOption = document.createElement('option');
                        fallbackOption.value = '';
                        fallbackOption.textContent = 'گزینه‌ای موجود نیست';
                        control.appendChild(fallbackOption);
                        control.disabled = true;
                    } else {
                        selectOptions.forEach(function (option) {
                            if (!option) {
                                return;
                            }
                            var optionValue = option.value !== undefined ? option.value : option;
                            var optionLabel = option.label !== undefined ? option.label : optionValue;
                            var opt = document.createElement('option');
                            opt.value = optionValue;
                            opt.textContent = optionLabel;
                            if (String(optionValue) === String(currentValue)) {
                                opt.selected = true;
                            }
                            control.appendChild(opt);
                        });
                    }
                    control.addEventListener('change', function () {
                        element.props[key] = control.value;
                        handleUpdate();
                    });
                    controlContainer.appendChild(control);
                    break;
                case 'multi_select':
                    control = document.createElement('div');
                    control.className = 'builder-multi-select';

                    var normalizeSelectionValue = function (input) {
                        var values = [];
                        if (Array.isArray(input)) {
                            input.forEach(function (item) {
                                if (item && typeof item === 'object') {
                                    if (Object.prototype.hasOwnProperty.call(item, 'value')) {
                                        item = item.value;
                                    } else if (Object.prototype.hasOwnProperty.call(item, 'id')) {
                                        item = item.id;
                                    }
                                }
                                if (item === undefined || item === null) {
                                    return;
                                }
                                var stringValue = String(item).trim();
                                if (stringValue === '') {
                                    return;
                                }
                                if (values.indexOf(stringValue) === -1) {
                                    values.push(stringValue);
                                }
                            });
                        } else if (typeof input === 'string') {
                            var trimmedInput = input.trim();
                            if (trimmedInput !== '') {
                                try {
                                    var parsedInput = JSON.parse(trimmedInput);
                                    if (Array.isArray(parsedInput)) {
                                        return normalizeSelectionValue(parsedInput);
                                    }
                                } catch (error) {
                                    // Ignore JSON parse error and fall back to comma-separated values.
                                }
                                trimmedInput.split(',').forEach(function (token) {
                                    var value = token.trim();
                                    if (value !== '' && values.indexOf(value) === -1) {
                                        values.push(value);
                                    }
                                });
                            }
                        }
                        return values;
                    };

                    var selectionLimit = typeof field.maxItems === 'number'
                        ? Math.max(1, Math.floor(field.maxItems))
                        : null;
                    var selectedValues = normalizeSelectionValue(currentValue);
                    if (selectionLimit !== null && selectedValues.length > selectionLimit) {
                        selectedValues = selectedValues.slice(0, selectionLimit);
                    }

                    var multiSelectOptions = resolveFieldOptions(field);
                    if (multiSelectOptions.length === 0) {
                        var emptyMessage = document.createElement('div');
                        emptyMessage.className = 'builder-multi-select-empty text-muted small';
                        emptyMessage.textContent = 'هیچ ابزاری برای انتخاب وجود ندارد.';
                        control.appendChild(emptyMessage);
                    } else {
                        multiSelectOptions.forEach(function (option) {
                            if (!option) {
                                return;
                            }

                            var optionValueRaw;
                            var optionLabel;
                            var optionDescription = '';
                            var optionMeta = option.meta || {};

                            if (typeof option === 'object') {
                                optionValueRaw = option.value !== undefined ? option.value : option.id;
                                optionLabel = option.label !== undefined ? option.label : option.name;
                                if (typeof option.description === 'string') {
                                    optionDescription = option.description;
                                }
                            } else {
                                optionValueRaw = option;
                                optionLabel = option;
                            }

                            if (optionValueRaw === undefined || optionValueRaw === null) {
                                return;
                            }

                            var optionValue = String(optionValueRaw).trim();
                            if (optionValue === '') {
                                return;
                            }

                            var checkboxId = generateId('assessment');
                            var formCheck = document.createElement('div');
                            formCheck.className = 'form-check builder-multi-option-item';

                            var checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.className = 'form-check-input';
                            checkbox.id = checkboxId;
                            checkbox.checked = selectedValues.indexOf(optionValue) !== -1;

                            var labelEl = document.createElement('label');
                            labelEl.className = 'form-check-label builder-multi-option';
                            labelEl.setAttribute('for', checkboxId);

                            var titleEl = document.createElement('span');
                            titleEl.className = 'builder-multi-option-title';
                            titleEl.textContent = optionLabel || optionValue;
                            labelEl.appendChild(titleEl);

                            // Only show the tool title in the picker per user request.

                            checkbox.addEventListener('change', function () {
                                var nextValues = selectedValues.slice();
                                if (checkbox.checked) {
                                    if (nextValues.indexOf(optionValue) === -1) {
                                        if (selectionLimit !== null && nextValues.length >= selectionLimit) {
                                            checkbox.checked = false;
                                            return;
                                        }
                                        nextValues.push(optionValue);
                                    }
                                } else {
                                    nextValues = nextValues.filter(function (value) {
                                        return value !== optionValue;
                                    });
                                }
                                selectedValues = nextValues;
                                element.props[key] = selectedValues.slice();
                                handleUpdate();
                            });

                            formCheck.appendChild(checkbox);
                            formCheck.appendChild(labelEl);
                            control.appendChild(formCheck);
                        });
                    }

                    controlContainer.appendChild(control);
                    break;
                case 'mbti_feature_picker':
                    control = document.createElement('div');
                    control.className = 'builder-mbti-feature-picker';

                    var availableCategories = getMbtiFeatureCategories();

                    var parseCategorySelection = function (input) {
                        if (input === null || input === undefined || input === '') {
                            return null;
                        }
                        if (Array.isArray(input)) {
                            var cleaned = [];
                            input.forEach(function (item) {
                                if (typeof item !== 'string' && typeof item !== 'number') {
                                    return;
                                }
                                var trimmed = String(item).trim();
                                if (trimmed === '' || cleaned.indexOf(trimmed) !== -1) {
                                    return;
                                }
                                cleaned.push(trimmed);
                            });
                            return cleaned;
                        }
                        if (typeof input === 'string') {
                            try {
                                var decoded = JSON.parse(input);
                                if (Array.isArray(decoded)) {
                                    return parseCategorySelection(decoded);
                                }
                            } catch (error) {
                                // Ignore JSON parsing issues and fall back to comma-separated parsing.
                            }
                            var parts = input.split(',');
                            return parseCategorySelection(parts);
                        }
                        return null;
                    };

                    var selectedCategories = parseCategorySelection(currentValue);
                    var checkboxRefs = [];

                    var reflectSelection = function () {
                        var selectionSet = null;
                        if (Array.isArray(selectedCategories)) {
                            selectionSet = {};
                            selectedCategories.forEach(function (category) {
                                selectionSet[category] = true;
                            });
                        }
                        checkboxRefs.forEach(function (ref) {
                            if (!ref || !ref.checkbox) {
                                return;
                            }
                            if (selectionSet === null) {
                                ref.checkbox.checked = true;
                            } else {
                                ref.checkbox.checked = !!selectionSet[ref.category];
                            }
                        });
                    };

                    var commitSelection = function (nextSelected) {
                        if (Array.isArray(nextSelected)) {
                            if (availableCategories.length > 0 && nextSelected.length === availableCategories.length) {
                                selectedCategories = null;
                                element.props[key] = null;
                            } else {
                                selectedCategories = nextSelected.slice();
                                element.props[key] = selectedCategories.slice();
                            }
                        } else if (nextSelected === null) {
                            selectedCategories = null;
                            element.props[key] = null;
                        } else {
                            selectedCategories = [];
                            element.props[key] = [];
                        }
                        handleUpdate();
                        reflectSelection();
                    };

                    if (availableCategories.length === 0) {
                        var emptyNotice = document.createElement('div');
                        emptyNotice.className = 'text-muted small';
                        emptyNotice.textContent = 'در حال حاضر دسته‌بندی مشخصی برای ویژگی‌های MBTI در دسترس نیست.';
                        control.appendChild(emptyNotice);
                    } else {
                        if (availableCategories.length > 1) {
                            var actionsWrapper = document.createElement('div');
                            actionsWrapper.className = 'd-flex flex-wrap gap-2 mb-2';

                            var selectAllBtn = document.createElement('button');
                            selectAllBtn.type = 'button';
                            selectAllBtn.className = 'btn btn-sm btn-outline-secondary';
                            selectAllBtn.textContent = 'انتخاب همه';
                            selectAllBtn.addEventListener('click', function () {
                                commitSelection(availableCategories.slice());
                            });

                            var clearBtn = document.createElement('button');
                            clearBtn.type = 'button';
                            clearBtn.className = 'btn btn-sm btn-outline-secondary';
                            clearBtn.textContent = 'حذف انتخاب‌ها';
                            clearBtn.addEventListener('click', function () {
                                commitSelection([]);
                            });

                            actionsWrapper.appendChild(selectAllBtn);
                            actionsWrapper.appendChild(clearBtn);
                            control.appendChild(actionsWrapper);
                        }

                        availableCategories.forEach(function (categoryTitle) {
                            var optionId = generateId('mbti-category');
                            var optionWrapper = document.createElement('div');
                            optionWrapper.className = 'form-check form-switch mb-1';

                            var checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.className = 'form-check-input';
                            checkbox.id = optionId;

                            var labelEl = document.createElement('label');
                            labelEl.className = 'form-check-label';
                            labelEl.setAttribute('for', optionId);
                            labelEl.textContent = categoryTitle;

                            checkbox.addEventListener('change', function () {
                                var workingSelection;
                                if (Array.isArray(selectedCategories)) {
                                    workingSelection = selectedCategories.slice();
                                } else {
                                    workingSelection = availableCategories.slice();
                                }

                                if (checkbox.checked) {
                                    if (workingSelection.indexOf(categoryTitle) === -1) {
                                        workingSelection.push(categoryTitle);
                                    }
                                } else {
                                    workingSelection = workingSelection.filter(function (item) {
                                        return item !== categoryTitle;
                                    });
                                }

                                var normalizedNext = availableCategories.filter(function (item) {
                                    return workingSelection.indexOf(item) !== -1;
                                });
                                commitSelection(normalizedNext);
                            });

                            optionWrapper.appendChild(checkbox);
                            optionWrapper.appendChild(labelEl);
                            control.appendChild(optionWrapper);

                            checkboxRefs.push({ category: categoryTitle, checkbox: checkbox });
                        });

                        reflectSelection();
                    }

                    controlContainer.appendChild(control);
                    break;
                case 'mbti_feature_labels':
                    control = document.createElement('div');
                    control.className = 'builder-mbti-feature-labels';

                    var labelCategories = getMbtiFeatureCategories();
                    var parseLabelMap = function (input) {
                        if (!input) {
                            return {};
                        }
                        if (typeof input === 'string') {
                            var trimmedInput = input.trim();
                            if (trimmedInput === '') {
                                return {};
                            }
                            try {
                                var decoded = JSON.parse(trimmedInput);
                                if (decoded && typeof decoded === 'object') {
                                    return parseLabelMap(decoded);
                                }
                            } catch (error) {
                                return {};
                            }
                            return {};
                        }
                        if (Array.isArray(input)) {
                            var mapFromArray = {};
                            input.forEach(function (entry) {
                                if (!entry || typeof entry !== 'object') {
                                    return;
                                }
                                if (Object.prototype.hasOwnProperty.call(entry, 'category') && Object.prototype.hasOwnProperty.call(entry, 'label')) {
                                    var categoryValue = entry.category;
                                    var labelValue = entry.label;
                                    if (typeof categoryValue !== 'string' && typeof categoryValue !== 'number') {
                                        return;
                                    }
                                    if (typeof labelValue !== 'string' && typeof labelValue !== 'number') {
                                        return;
                                    }
                                    var categoryKey = String(categoryValue).trim();
                                    var labelText = String(labelValue).trim();
                                    if (categoryKey === '' || labelText === '') {
                                        return;
                                    }
                                    mapFromArray[categoryKey] = labelText;
                                }
                            });
                            return mapFromArray;
                        }
                        if (typeof input === 'object') {
                            var cleanObject = {};
                            Object.keys(input).forEach(function (key) {
                                if (typeof key !== 'string') {
                                    return;
                                }
                                var labelCandidate = input[key];
                                if (typeof labelCandidate !== 'string' && typeof labelCandidate !== 'number') {
                                    return;
                                }
                                var trimmedLabel = String(labelCandidate).trim();
                                if (trimmedLabel === '') {
                                    return;
                                }
                                cleanObject[key.trim()] = trimmedLabel;
                            });
                            return cleanObject;
                        }
                        return {};
                    };

                    var labelMap = parseLabelMap(currentValue);

                    if (labelCategories.length === 0) {
                        var emptyLabelNotice = document.createElement('div');
                        emptyLabelNotice.className = 'text-muted small';
                        emptyLabelNotice.textContent = 'هیچ دسته‌ای برای تنظیم عنوان فارسی در دسترس نیست.';
                        control.appendChild(emptyLabelNotice);
                    } else {
                        var helperText = document.createElement('div');
                        helperText.className = 'text-muted small mb-2';
                        helperText.textContent = 'اگر فیلد را خالی بگذارید عنوان اصلی دسته نمایش داده می‌شود.';
                        control.appendChild(helperText);

                        labelCategories.forEach(function (categoryTitle) {
                            var fieldWrapper = document.createElement('div');
                            fieldWrapper.className = 'mb-2';

                            var labelEl = document.createElement('label');
                            labelEl.className = 'form-label small mb-1';
                            labelEl.textContent = 'عنوان «' + categoryTitle + '»';
                            fieldWrapper.appendChild(labelEl);

                            var inputEl = document.createElement('input');
                            inputEl.type = 'text';
                            inputEl.className = 'form-control form-control-sm';
                            inputEl.value = labelMap[categoryTitle] || '';
                            inputEl.maxLength = 160;
                            inputEl.placeholder = 'مثلاً عنوان فارسی دسته';

                            inputEl.addEventListener('input', function () {
                                var trimmed = inputEl.value.trim();
                                if (trimmed === '') {
                                    if (Object.prototype.hasOwnProperty.call(labelMap, categoryTitle)) {
                                        delete labelMap[categoryTitle];
                                        element.props[key] = Object.keys(labelMap).length ? Object.assign({}, labelMap) : {};
                                        handleUpdate();
                                    }
                                    return;
                                }
                                if (labelMap[categoryTitle] === trimmed) {
                                    return;
                                }
                                labelMap[categoryTitle] = trimmed;
                                element.props[key] = Object.assign({}, labelMap);
                                handleUpdate();
                            });

                            fieldWrapper.appendChild(inputEl);
                            control.appendChild(fieldWrapper);
                        });
                    }

                    controlContainer.appendChild(control);
                    break;
                case 'number':
                    control = document.createElement('input');
                    control.type = 'number';
                    control.className = 'form-control';
                    if (field.min !== undefined) {
                        control.min = String(field.min);
                    }
                    if (field.max !== undefined) {
                        control.max = String(field.max);
                    }
                    if (field.step !== undefined) {
                        control.step = String(field.step);
                    }
                    control.value = currentValue !== undefined ? currentValue : '';
                    control.addEventListener('input', function () {
                        var value = control.value !== '' ? Number(control.value) : '';
                        if (value === '') {
                            element.props[key] = '';
                            handleUpdate();
                            return;
                        }
                        if (!isFinite(value)) {
                            value = field.min !== undefined ? Number(field.min) : 0;
                        }
                        if (field.min !== undefined && value < field.min) {
                            value = Number(field.min);
                        }
                        if (field.max !== undefined && value > field.max) {
                            value = Number(field.max);
                        }
                        if (field.cast === 'int') {
                            value = Math.round(value);
                        }
                        element.props[key] = value;
                        control.value = value;
                        handleUpdate();
                    });
                    controlContainer.appendChild(control);
                    break;
                case 'textarea':
                    control = document.createElement('textarea');
                    control.className = 'form-control';
                    control.rows = field.rows || 3;
                    if (field.placeholder) {
                        control.placeholder = field.placeholder;
                    }
                    if (field.maxLength) {
                        control.maxLength = field.maxLength;
                    }
                    control.value = currentValue !== undefined ? currentValue : '';
                    var textareaCounter = null;
                    if (field.maxLength) {
                        textareaCounter = document.createElement('div');
                        textareaCounter.className = 'text-muted small mt-1 text-end';
                        var updateCounter = function () {
                            textareaCounter.textContent = control.value.length + ' / ' + field.maxLength;
                        };
                        updateCounter();
                        control.addEventListener('input', updateCounter);
                    }
                    control.addEventListener('input', function () {
                        element.props[key] = control.value;
                        handleUpdate();
                    });
                    controlContainer.appendChild(control);
                    if (textareaCounter) {
                        controlContainer.appendChild(textareaCounter);
                    }
                    break;
                case 'color':
                    control = document.createElement('div');
                    control.className = 'builder-color-input d-flex align-items-center gap-2';

                    // Color Picker (visual)
                    var colorPicker = document.createElement('input');
                    colorPicker.type = 'color';
                    colorPicker.className = 'builder-color-picker';
                    colorPicker.title = 'انتخاب رنگ';
                    colorPicker.value = (currentValue && currentValue.match(/^#[0-9A-Fa-f]{6}$/)) ? currentValue : '#000000';

                    // Color swatch preview
                    var swatch = document.createElement('span');
                    swatch.className = 'builder-color-swatch';
                    swatch.title = 'پیش‌نمایش رنگ';

                    // Text input for color code
                    var colorInput = document.createElement('input');
                    colorInput.type = 'text';
                    colorInput.className = 'form-control';
                    if (field.placeholder) {
                        colorInput.placeholder = field.placeholder;
                    } else {
                        colorInput.placeholder = '#000000';
                    }
                    if (field.maxLength) {
                        colorInput.maxLength = field.maxLength;
                    }
                    colorInput.value = currentValue || '';

                    var applyColorPreview = function (value) {
                        if (typeof value === 'string' && value.trim() !== '') {
                            swatch.style.backgroundColor = value;
                            // Update color picker if valid hex color
                            if (value.match(/^#[0-9A-Fa-f]{6}$/)) {
                                colorPicker.value = value;
                            }
                        } else {
                            swatch.style.backgroundColor = 'transparent';
                        }
                    };
                    applyColorPreview(colorInput.value);

                    // When color picker changes, update text input
                    colorPicker.addEventListener('input', function () {
                        colorInput.value = colorPicker.value;
                        element.props[key] = colorPicker.value;
                        applyColorPreview(colorPicker.value);
                        handleUpdate();
                    });

                    // When text input changes, update preview and picker
                    colorInput.addEventListener('input', function () {
                        element.props[key] = colorInput.value;
                        applyColorPreview(colorInput.value);
                        handleUpdate();
                    });

                    control.appendChild(colorPicker);
                    control.appendChild(swatch);
                    control.appendChild(colorInput);
                    controlContainer.appendChild(control);
                    break;
                case 'font_size':
                    // Font size selector with preset sizes and custom input
                    control = document.createElement('div');
                    control.className = 'builder-font-size-input d-flex align-items-center gap-2';

                    // Quick size buttons
                    var quickSizes = [8, 10, 12, 14, 16, 18, 20, 24, 28, 32, 36, 42, 48, 56, 64, 72];
                    var quickSizeContainer = document.createElement('div');
                    quickSizeContainer.className = 'builder-font-size-presets d-flex flex-wrap gap-1 mb-2';
                    
                    quickSizes.forEach(function(size) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'btn btn-sm btn-outline-secondary builder-font-size-preset-btn';
                        btn.textContent = size;
                        btn.title = size + 'px';
                        
                        if (currentValue == size) {
                            btn.classList.add('active');
                        }
                        
                        btn.addEventListener('click', function() {
                            // Remove active from all buttons
                            quickSizeContainer.querySelectorAll('.builder-font-size-preset-btn').forEach(function(b) {
                                b.classList.remove('active');
                            });
                            btn.classList.add('active');
                            
                            // Update values
                            fontSizeInput.value = size;
                            element.props[key] = size;
                            handleUpdate();
                        });
                        
                        quickSizeContainer.appendChild(btn);
                    });

                    // Custom input with unit
                    var inputWrapper = document.createElement('div');
                    inputWrapper.className = 'd-flex align-items-center gap-2 w-100';
                    
                    var fontSizeInput = document.createElement('input');
                    fontSizeInput.type = 'number';
                    fontSizeInput.className = 'form-control';
                    fontSizeInput.placeholder = 'اندازه دلخواه';
                    fontSizeInput.min = '6';
                    fontSizeInput.max = '200';
                    fontSizeInput.step = '1';
                    fontSizeInput.value = currentValue || '16';
                    
                    var unitLabel = document.createElement('span');
                    unitLabel.className = 'text-muted';
                    unitLabel.textContent = 'px';
                    
                    fontSizeInput.addEventListener('input', function() {
                        var value = fontSizeInput.value !== '' ? Number(fontSizeInput.value) : 16;
                        
                        // Validate range
                        if (value < 6) value = 6;
                        if (value > 200) value = 200;
                        
                        fontSizeInput.value = value;
                        element.props[key] = value;
                        
                        // Update preset buttons
                        quickSizeContainer.querySelectorAll('.builder-font-size-preset-btn').forEach(function(b) {
                            if (Number(b.textContent) === value) {
                                b.classList.add('active');
                            } else {
                                b.classList.remove('active');
                            }
                        });
                        
                        handleUpdate();
                    });
                    
                    inputWrapper.appendChild(fontSizeInput);
                    inputWrapper.appendChild(unitLabel);
                    
                    control.appendChild(quickSizeContainer);
                    control.appendChild(inputWrapper);
                    controlContainer.appendChild(control);
                    break;
                case 'table_editor':
                    var defaultTable = definition.defaultProps && definition.defaultProps[key]
                        ? normalizeTableData(definition.defaultProps[key])
                        : normalizeTableData(element.props[key]);
                    var editorState = normalizeTableData(currentValue);
                    element.props[key] = JSON.parse(JSON.stringify(editorState));

                    var cloneTableState = function () {
                        return {
                            columns: editorState.columns.slice(),
                            rows: editorState.rows.map(function (row) {
                                return row.slice();
                            })
                        };
                    };

                    control = document.createElement('div');
                    control.className = 'builder-table-editor';

                    var controlsBar = document.createElement('div');
                    controlsBar.className = 'builder-table-editor-controls';

                    var addColumnBtn = document.createElement('button');
                    addColumnBtn.type = 'button';
                    addColumnBtn.className = 'btn btn-sm btn-light';
                    addColumnBtn.innerHTML = '<ion-icon name="add-outline"></ion-icon><span>ستون جدید</span>';
                    addColumnBtn.addEventListener('click', function () {
                        if (editorState.columns.length >= 8) {
                            return;
                        }
                        editorState.columns.push('ستون ' + (editorState.columns.length + 1));
                        editorState.rows.forEach(function (row) {
                            row.push('');
                        });
                        element.props[key] = cloneTableState();
                        handleUpdate();
                        renderEditor();
                    });
                    controlsBar.appendChild(addColumnBtn);

                    var addRowBtn = document.createElement('button');
                    addRowBtn.type = 'button';
                    addRowBtn.className = 'btn btn-sm btn-light';
                    addRowBtn.innerHTML = '<ion-icon name="add-outline"></ion-icon><span>ردیف جدید</span>';
                    addRowBtn.addEventListener('click', function () {
                        if (editorState.rows.length >= 20) {
                            return;
                        }
                        editorState.rows.push(new Array(editorState.columns.length).fill(''));
                        element.props[key] = cloneTableState();
                        handleUpdate();
                        renderEditor();
                    });
                    controlsBar.appendChild(addRowBtn);

                    var resetBtn = document.createElement('button');
                    resetBtn.type = 'button';
                    resetBtn.className = 'btn btn-sm btn-outline-secondary';
                    resetBtn.innerHTML = '<ion-icon name="refresh-outline"></ion-icon><span>بازنشانی جدول</span>';
                    resetBtn.addEventListener('click', function () {
                        editorState = normalizeTableData(defaultTable);
                        element.props[key] = cloneTableState();
                        handleUpdate();
                        renderEditor();
                    });
                    controlsBar.appendChild(resetBtn);

                    control.appendChild(controlsBar);

                    var tableWrapper = document.createElement('div');
                    tableWrapper.className = 'builder-table-editor-table-wrapper';
                    control.appendChild(tableWrapper);

                    var renderEditor = function () {
                        tableWrapper.innerHTML = '';

                        var table = document.createElement('table');
                        table.className = 'builder-table-editor-table';

                        var thead = document.createElement('thead');
                        var headerRow = document.createElement('tr');

                        var indexHeader = document.createElement('th');
                        indexHeader.className = 'builder-table-editor-index';
                        indexHeader.textContent = '#';
                        headerRow.appendChild(indexHeader);

                        editorState.columns.forEach(function (columnTitle, columnIndex) {
                            var th = document.createElement('th');
                            th.className = 'builder-table-editor-header-cell';

                            var headerInput = document.createElement('input');
                            headerInput.type = 'text';
                            headerInput.className = 'form-control form-control-sm';
                            headerInput.value = columnTitle;
                            headerInput.addEventListener('input', function () {
                                editorState.columns[columnIndex] = headerInput.value;
                                element.props[key] = cloneTableState();
                                updateStateInput();
                                renderCanvas();
                            });
                            th.appendChild(headerInput);

                            if (editorState.columns.length > 1) {
                                var removeColBtn = document.createElement('button');
                                removeColBtn.type = 'button';
                                removeColBtn.className = 'btn btn-xs btn-link text-danger p-0 builder-table-editor-remove-column';
                                removeColBtn.innerHTML = '<ion-icon name="close-circle-outline"></ion-icon>';
                                removeColBtn.addEventListener('click', function () {
                                    if (editorState.columns.length <= 1) {
                                        return;
                                    }
                                    editorState.columns.splice(columnIndex, 1);
                                    editorState.rows.forEach(function (row) {
                                        row.splice(columnIndex, 1);
                                        if (row.length === 0) {
                                            row.push('');
                                        }
                                    });
                                    element.props[key] = cloneTableState();
                                    handleUpdate();
                                    renderEditor();
                                });
                                th.appendChild(removeColBtn);
                            }

                            headerRow.appendChild(th);
                        });

                        var actionsHeader = document.createElement('th');
                        actionsHeader.className = 'builder-table-editor-actions';
                        actionsHeader.textContent = 'حذف';
                        headerRow.appendChild(actionsHeader);

                        thead.appendChild(headerRow);
                        table.appendChild(thead);

                        var tbody = document.createElement('tbody');

                        if (!Array.isArray(editorState.rows) || editorState.rows.length === 0) {
                            editorState.rows = [new Array(editorState.columns.length).fill('')];
                        }

                        editorState.rows.forEach(function (rowValues, rowIndex) {
                            var tr = document.createElement('tr');

                            var indexCell = document.createElement('td');
                            indexCell.className = 'builder-table-editor-index';
                            indexCell.textContent = String(rowIndex + 1);
                            tr.appendChild(indexCell);

                            editorState.columns.forEach(function (columnTitle, columnIndex) {
                                var td = document.createElement('td');
                                td.className = 'builder-table-editor-cell';
                                var cellInput = document.createElement('textarea');
                                cellInput.className = 'form-control form-control-sm';
                                cellInput.rows = 1;
                                cellInput.value = rowValues[columnIndex] || '';
                                cellInput.addEventListener('input', function () {
                                    editorState.rows[rowIndex][columnIndex] = cellInput.value;
                                    element.props[key] = cloneTableState();
                                    updateStateInput();
                                    renderCanvas();
                                });
                                td.appendChild(cellInput);
                                tr.appendChild(td);
                            });

                            var removeRowCell = document.createElement('td');
                            removeRowCell.className = 'builder-table-editor-actions';
                            var removeRowBtn = document.createElement('button');
                            removeRowBtn.type = 'button';
                            removeRowBtn.className = 'btn btn-sm btn-link text-danger';
                            removeRowBtn.textContent = 'حذف';
                            removeRowBtn.addEventListener('click', function () {
                                if (editorState.rows.length <= 1) {
                                    editorState.rows[0] = new Array(editorState.columns.length).fill('');
                                } else {
                                    editorState.rows.splice(rowIndex, 1);
                                }
                                element.props[key] = cloneTableState();
                                handleUpdate();
                                renderEditor();
                            });
                            removeRowCell.appendChild(removeRowBtn);
                            tr.appendChild(removeRowCell);

                            tbody.appendChild(tr);
                        });

                        table.appendChild(tbody);
                        tableWrapper.appendChild(table);

                        addColumnBtn.disabled = editorState.columns.length >= 8;
                        addRowBtn.disabled = editorState.rows.length >= 20;
                    };

                    renderEditor();
                    controlContainer.appendChild(control);
                    break;
                case 'list':
                    control = document.createElement('div');
                    control.className = 'builder-list-editor';
                    var listState = Array.isArray(currentValue) ? currentValue.slice() : [];
                    var maxItems = typeof field.maxItems === 'number' && field.maxItems > 0 ? Math.min(field.maxItems, 24) : 12;
                    var itemMaxLength = typeof field.itemMaxLength === 'number' && field.itemMaxLength > 0 ? field.itemMaxLength : 240;

                    var renderListEditor = function () {
                        control.innerHTML = '';

                        var itemsContainer = document.createElement('div');
                        itemsContainer.className = 'builder-list-editor-items';

                        if (listState.length === 0) {
                            listState.push('');
                        }

                        listState.forEach(function (itemValue, itemIndex) {
                            var itemRow = document.createElement('div');
                            itemRow.className = 'builder-list-editor-row';

                            var input = document.createElement('input');
                            input.type = 'text';
                            input.className = 'form-control';
                            input.placeholder = 'مورد ' + (itemIndex + 1);
                            if (itemMaxLength) {
                                input.maxLength = itemMaxLength;
                            }
                            input.value = itemValue || '';
                            input.addEventListener('input', function () {
                                listState[itemIndex] = input.value;
                                element.props[key] = listState.slice();
                                handleUpdate();
                            });
                            itemRow.appendChild(input);

                            var rowActions = document.createElement('div');
                            rowActions.className = 'builder-list-editor-row-actions';

                            var removeBtn = document.createElement('button');
                            removeBtn.type = 'button';
                            removeBtn.className = 'btn btn-sm btn-link text-danger';
                            removeBtn.textContent = 'حذف';
                            removeBtn.addEventListener('click', function () {
                                if (listState.length <= 1) {
                                    listState[0] = '';
                                } else {
                                    listState.splice(itemIndex, 1);
                                }
                                element.props[key] = listState.slice();
                                handleUpdate();
                                renderListEditor();
                            });
                            rowActions.appendChild(removeBtn);

                            itemRow.appendChild(rowActions);
                            itemsContainer.appendChild(itemRow);
                        });

                        control.appendChild(itemsContainer);

                        var footer = document.createElement('div');
                        footer.className = 'builder-list-editor-footer';

                        var addBtn = document.createElement('button');
                        addBtn.type = 'button';
                        addBtn.className = 'btn btn-sm btn-outline-main';
                        addBtn.innerHTML = '<ion-icon name="add-outline"></ion-icon><span>افزودن مورد</span>';
                        addBtn.disabled = listState.length >= maxItems;
                        addBtn.addEventListener('click', function () {
                            if (listState.length >= maxItems) {
                                return;
                            }
                            listState.push('');
                            element.props[key] = listState.slice();
                            handleUpdate();
                            renderListEditor();
                        });
                        footer.appendChild(addBtn);

                        var countInfo = document.createElement('div');
                        countInfo.className = 'text-muted small ms-auto';
                        countInfo.textContent = listState.filter(function (value) { return String(value).trim() !== ''; }).length + ' از ' + maxItems;
                        footer.appendChild(countInfo);

                        control.appendChild(footer);
                    };

                    renderListEditor();
                    controlContainer.appendChild(control);
                    break;
                default:
                    var textInput = document.createElement('input');
                    textInput.type = 'text';
                    textInput.className = 'form-control';
                    if (field.placeholder) {
                        textInput.placeholder = field.placeholder;
                    }
                    if (field.maxLength) {
                        textInput.maxLength = field.maxLength;
                    }
                    textInput.value = currentValue !== undefined ? currentValue : '';
                    controlContainer.appendChild(textInput);

                    var updateButtonsState = null;

                    if (field.allowUpload && uploadEndpoint) {
                        var uploadControls = document.createElement('div');
                        uploadControls.className = 'builder-upload-controls';

                        var uploadButton = document.createElement('button');
                        uploadButton.type = 'button';
                        uploadButton.className = 'btn btn-sm btn-outline-main';
                        uploadButton.textContent = field.uploadLabel || 'آپلود تصویر';
                        uploadControls.appendChild(uploadButton);

                        var clearButton = document.createElement('button');
                        clearButton.type = 'button';
                        clearButton.className = 'btn btn-sm btn-outline-danger';
                        clearButton.textContent = field.clearLabel || 'حذف تصویر';
                        uploadControls.appendChild(clearButton);

                        controlContainer.appendChild(uploadControls);

                        var statusEl = document.createElement('div');
                        statusEl.className = 'builder-upload-status small mt-2 text-muted';
                        statusEl.textContent = '';
                        controlContainer.appendChild(statusEl);

                        var hiddenFileInput = document.createElement('input');
                        hiddenFileInput.type = 'file';
                        hiddenFileInput.accept = field.accept || 'image/*';
                        hiddenFileInput.className = 'd-none';
                        controlContainer.appendChild(hiddenFileInput);

                        var setStatus = function (message, tone) {
                            var baseClass = 'builder-upload-status small mt-2';
                            var toneClass = tone === 'success'
                                ? ' text-success'
                                : tone === 'error'
                                    ? ' text-danger'
                                    : ' text-muted';
                            statusEl.className = baseClass + toneClass;
                            statusEl.textContent = message || '';
                        };

                        var maxFileSizeFromField = typeof field.maxFileSize === 'number'
                            ? field.maxFileSize
                            : (5 * 1024 * 1024);

                        updateButtonsState = function () {
                            clearButton.disabled = textInput.value.trim() === '';
                        };

                        uploadButton.addEventListener('click', function () {
                            hiddenFileInput.click();
                        });

                        clearButton.addEventListener('click', function () {
                            textInput.value = '';
                            element.props[key] = '';
                            handleUpdate();
                            setStatus('تصویر حذف شد.', 'muted');
                            hiddenFileInput.value = '';
                            updateButtonsState();
                        });

                        hiddenFileInput.addEventListener('change', function () {
                            var file = hiddenFileInput.files && hiddenFileInput.files[0] ? hiddenFileInput.files[0] : null;
                            if (!file) {
                                return;
                            }

                            if (file.size > maxFileSizeFromField) {
                                setStatus('حجم فایل نباید بیشتر از ۵ مگابایت باشد.', 'error');
                                hiddenFileInput.value = '';
                                return;
                            }

                            if (!/^image\//i.test(file.type || '')) {
                                setStatus('فقط فایل‌های تصویری مجاز هستند.', 'error');
                                hiddenFileInput.value = '';
                                return;
                            }

                            if (!csrfToken) {
                                setStatus('توکن امنیتی یافت نشد. صفحه را بازنشانی کنید.', 'error');
                                hiddenFileInput.value = '';
                                return;
                            }

                            var formData = new FormData();
                            formData.append('image', file);
                            formData.append('_token', csrfToken);

                            uploadButton.disabled = true;
                            clearButton.disabled = true;
                            setStatus('در حال آپلود تصویر...', 'muted');

                            fetch(uploadEndpoint, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                                .then(function (response) {
                                    return response.json().catch(function () {
                                        return null;
                                    }).then(function (payload) {
                                        return { ok: response.ok, payload: payload };
                                    });
                                })
                                .then(function (result) {
                                    if (!result || !result.payload) {
                                        throw new Error('در آپلود تصویر خطایی رخ داد.');
                                    }
                                    if (!result.ok || !result.payload.success || !result.payload.url) {
                                        var errorMessage = result.payload.message || 'در آپلود تصویر خطایی رخ داد.';
                                        throw new Error(errorMessage);
                                    }
                                    textInput.value = result.payload.url;
                                    element.props[key] = result.payload.url;
                                    handleUpdate();
                                    setStatus('تصویر با موفقیت آپلود شد.', 'success');
                                })
                                .catch(function (error) {
                                    setStatus(error && error.message ? error.message : 'در آپلود تصویر خطایی رخ داد.', 'error');
                                })
                                .finally(function () {
                                    uploadButton.disabled = false;
                                    hiddenFileInput.value = '';
                                    updateButtonsState();
                                });
                        });

                        updateButtonsState();
                    }

                    var onTextInput = function () {
                        element.props[key] = textInput.value;
                        handleUpdate();
                        if (typeof updateButtonsState === 'function') {
                            updateButtonsState();
                        }
                    };

                    textInput.addEventListener('input', onTextInput);
                    control = textInput;

                    if (typeof updateButtonsState === 'function') {
                        updateButtonsState();
                    }
                    break;
            }

            if (helpText) {
                controlContainer.appendChild(helpText);
            }

            wrapper.appendChild(controlContainer);
            form.appendChild(wrapper);

            fieldMetadata.push({
                field: field,
                wrapper: wrapper
            });
        });

        var globalWrapper = document.createElement('div');
        globalWrapper.className = 'mb-3 builder-settings-field';

        var globalLabel = document.createElement('label');
        globalLabel.className = 'form-label fw-semibold';
        globalLabel.textContent = 'نمایش در تمام صفحات';
        globalWrapper.appendChild(globalLabel);

        var toggleContainer = document.createElement('div');
        toggleContainer.className = 'form-check form-switch';

        var toggleInput = document.createElement('input');
        toggleInput.type = 'checkbox';
        toggleInput.className = 'form-check-input';
        toggleInput.checked = normalizeBoolean(element.props.applyToAllPages);
        toggleInput.addEventListener('change', function () {
            element.props.applyToAllPages = toggleInput.checked ? 1 : 0;
            updateStateInput();
            renderCanvas();
        });
        toggleContainer.appendChild(toggleInput);
        globalWrapper.appendChild(toggleContainer);

        var toggleHelp = document.createElement('div');
        toggleHelp.className = 'form-text text-muted';
    toggleHelp.textContent = 'در صورت فعال بودن، این آیتم در تمام صفحات فعلی و صفحه‌های جدید گواهی نمایش داده می‌شود.';
        globalWrapper.appendChild(toggleHelp);

        form.appendChild(globalWrapper);

        elementSettingsModalBody.appendChild(form);
        refreshFieldVisibility();
    };

    var renderAll = function () {
        ensureStateIntegrity();
        renderPageList();
        renderCanvas();
        renderElementSettings();
        renderPageLayoutControls();
        updateStateInput();
    };

    if (addPageButton) {
        addPageButton.addEventListener('click', function () {
            addPage();
        });
    }

    if (dropZoneEl) {
        dropZoneEl.addEventListener('dragover', function (event) {
            event.preventDefault();
            if (dropPlaceholderEl) {
                dropPlaceholderEl.classList.add('drag-over');
            }
        });

        dropZoneEl.addEventListener('dragleave', function () {
            if (dropPlaceholderEl) {
                dropPlaceholderEl.classList.remove('drag-over');
            }
        });

        dropZoneEl.addEventListener('drop', function (event) {
            event.preventDefault();
            if (dropPlaceholderEl) {
                dropPlaceholderEl.classList.remove('drag-over');
            }
            var type = event.dataTransfer.getData('component-type');
            if (type) {
                addElementToActivePage(type);
            }
        });

        dropZoneEl.addEventListener('click', function (event) {
            if (event.target === dropZoneEl || (dropPlaceholderEl && event.target === dropPlaceholderEl)) {
                selectedElementId = null;
                renderCanvas();
                renderElementSettings();
            }
        });
    }

    if (elementSettingsApplyButton) {
        elementSettingsApplyButton.addEventListener('click', function () {
            hideElementSettingsModal();
        });
    }

    if (elementSettingsModal) {
        elementSettingsModal.addEventListener('click', function (event) {
            if (!isModalFallback) {
                return;
            }
            var dismissTrigger = event.target.closest('[data-bs-dismiss="modal"]');
            if (dismissTrigger || event.target === elementSettingsModal) {
                event.preventDefault();
                hideElementSettingsModal();
            }
        });
    }

    renderComponentPalette();
    renderAll();
})();
