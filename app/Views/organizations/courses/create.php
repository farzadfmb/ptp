<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ایجاد دوره جدید';
$user = $user ?? [];

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
include __DIR__ . '/../../layouts/organization-navbar.php';
?>

<style>
    .course-form-card {
        background: white;
        border-radius: 20px;
        padding: 32px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
    }
    
    .form-section {
        margin-bottom: 32px;
        padding-bottom: 32px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .form-section:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    
    .form-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-section-title ion-icon {
        font-size: 24px;
        color: #667eea;
    }
    
    .image-preview-container {
        width: 100%;
        max-width: 400px;
        height: 250px;
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .image-preview-container:hover {
        border-color: #667eea;
        background: #f0f4ff;
    }
    
    .image-preview-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .image-preview-placeholder {
        text-align: center;
        color: #94a3b8;
    }
    
    .image-preview-placeholder ion-icon {
        font-size: 64px;
        margin-bottom: 12px;
        color: #cbd5e1;
    }
    
    .remove-image-btn {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 20px;
    }
    
    .image-preview-container.has-image .remove-image-btn {
        display: flex;
    }
    
    .form-hint {
        font-size: 13px;
        color: #64748b;
        margin-top: 6px;
    }
    
    .required-mark {
        color: #ef4444;
        margin-right: 4px;
    }
    
    .persian-datepicker-container {
        direction: rtl;
        font-family: 'Vazir', 'Yekan', sans-serif;
    }
    
    .persian-datepicker {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        padding: 12px;
        z-index: 9999;
    }
    
    .persian-datepicker .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px;
        margin-bottom: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px;
        color: white;
    }
    
    .persian-datepicker .header button {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .persian-datepicker .header button:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    .persian-datepicker .weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
        margin-bottom: 8px;
    }
    
    .persian-datepicker .weekday {
        text-align: center;
        padding: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
    }
    
    .persian-datepicker .days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
    }
    
    .persian-datepicker .day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s ease;
        color: #1e293b;
    }
    
    .persian-datepicker .day:hover {
        background: #f0f4ff;
        color: #667eea;
    }
    
    .persian-datepicker .day.other-month {
        color: #cbd5e1;
    }
    
    .persian-datepicker .day.selected {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .persian-datepicker .day.today {
        border: 2px solid #667eea;
    }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="d-flex align-items-center justify-content-between mb-24">
            <div>
                <h2 class="mb-8">ایجاد دوره جدید</h2>
                <p class="text-muted mb-0">اطلاعات دوره آموزشی را وارد کنید</p>
            </div>
            <a href="<?= UtilityHelper::baseUrl('organizations/courses'); ?>" class="btn btn-outline-secondary rounded-pill px-20">
                <ion-icon name="arrow-forward-outline"></ion-icon>
                بازگشت
            </a>
        </div>

        <form action="<?= UtilityHelper::baseUrl('organizations/courses'); ?>" method="POST" enctype="multipart/form-data">
            <div class="course-form-card">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <ion-icon name="information-circle-outline"></ion-icon>
                        اطلاعات پایه
                    </h3>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">
                                عنوان دوره
                                <span class="required-mark">*</span>
                            </label>
                            <input type="text" name="title" class="form-control" required placeholder="مثال: دوره جامع مدیریت پروژه">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">توضیحات دوره</label>
                            <textarea name="description" class="form-control" rows="5" placeholder="توضیحات کامل درباره دوره، اهداف آموزشی و مخاطبان"></textarea>
                            <div class="form-hint">توضیحات کامل و جذاب برای دوره خود بنویسید</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">دسته‌بندی</label>
                            <input type="text" name="category" class="form-control" placeholder="مثال: مدیریت، فناوری، مهارت‌های نرم">
                            <div class="form-hint">دسته‌بندی برای گروه‌بندی دوره‌ها</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">نام مدرس</label>
                            <input type="text" name="instructor_name" class="form-control" placeholder="نام مدرس یا تیم تدریس">
                        </div>
                    </div>
                </div>

                <!-- Course Details -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <ion-icon name="settings-outline"></ion-icon>
                        جزئیات دوره
                    </h3>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">قیمت (تومان)</label>
                            <input type="number" name="price" class="form-control" min="0" step="1000" value="0" placeholder="0">
                            <div class="form-hint">برای دوره رایگان عدد 0 وارد کنید</div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">مدت زمان (ساعت)</label>
                            <input type="number" name="duration_hours" class="form-control" min="0" value="0" placeholder="0">
                            <div class="form-hint">تقریبی کل ساعات دوره</div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">ترتیب نمایش</label>
                            <input type="number" name="sort_order" class="form-control" value="0" placeholder="0">
                            <div class="form-hint">عدد کمتر در ابتدا نمایش داده می‌شود</div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">وضعیت دوره</label>
                            <select name="status" class="form-select">
                                <option value="draft">پیش‌نویس</option>
                                <option value="published">منتشر شده</option>
                                <option value="presale">پیش‌فروش</option>
                                <option value="archived">بایگانی شده</option>
                            </select>
                            <div class="form-hint">فقط دوره‌های منتشر شده برای کاربران قابل مشاهده است</div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">تاریخ انتشار</label>
                            <div style="position: relative;">
                                <input type="text" name="published_at_display" id="jalaliDateInput" class="form-control" placeholder="انتخاب تاریخ" autocomplete="off" readonly style="cursor: pointer;">
                                <input type="hidden" name="published_at" id="gregorianDateInput">
                                <div id="persianDatepicker" class="persian-datepicker" style="display: none; position: absolute; top: 100%; left: 0; margin-top: 8px; z-index: 1000;"></div>
                            </div>
                            <div class="form-hint">تاریخ انتشار یا شروع دوره (شمسی)</div>
                        </div>
                    </div>
                </div>

                <!-- Cover Image -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <ion-icon name="image-outline"></ion-icon>
                        عکس کاور دوره
                    </h3>
                    
                    <div class="image-preview-container" id="imagePreviewContainer" onclick="document.getElementById('coverImageInput').click()">
                        <div class="image-preview-placeholder">
                            <ion-icon name="cloud-upload-outline"></ion-icon>
                            <p class="mb-0">کلیک کنید یا عکس را اینجا بکشید</p>
                            <small>JPG, PNG یا GIF - حداکثر 5MB</small>
                        </div>
                        <img id="imagePreview" style="display: none;" alt="Preview">
                        <button type="button" class="remove-image-btn" id="removeImageBtn" onclick="event.stopPropagation(); removeImage()">
                            <ion-icon name="close-outline"></ion-icon>
                        </button>
                    </div>
                    <input type="file" name="cover_image" id="coverImageInput" class="d-none" accept="image/*" onchange="previewImage(event)">
                    <div class="form-hint mt-2">ابعاد پیشنهادی: 1200x600 پیکسل</div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-3 justify-content-end mt-4">
                    <a href="<?= UtilityHelper::baseUrl('organizations/courses'); ?>" class="btn btn-outline-secondary rounded-pill px-24">
                        انصراف
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-24 d-flex align-items-center gap-2">
                        <ion-icon name="checkmark-circle-outline" style="font-size: 18px;"></ion-icon>
                        ایجاد دوره
                    </button>
                </div>
            </div>
        </form>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            const container = document.getElementById('imagePreviewContainer');
            const placeholder = container.querySelector('.image-preview-placeholder');
            
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
            container.classList.add('has-image');
        };
        reader.readAsDataURL(file);
    }
}

function removeImage() {
    const preview = document.getElementById('imagePreview');
    const container = document.getElementById('imagePreviewContainer');
    const placeholder = container.querySelector('.image-preview-placeholder');
    const input = document.getElementById('coverImageInput');
    
    preview.src = '';
    preview.style.display = 'none';
    placeholder.style.display = 'block';
    container.classList.remove('has-image');
    input.value = '';
}

// Drag and drop
const container = document.getElementById('imagePreviewContainer');

container.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.style.borderColor = '#667eea';
    this.style.background = '#f0f4ff';
});

container.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.style.borderColor = '#cbd5e1';
    this.style.background = '#f8fafc';
});

container.addEventListener('drop', function(e) {
    e.preventDefault();
    this.style.borderColor = '#cbd5e1';
    this.style.background = '#f8fafc';
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const input = document.getElementById('coverImageInput');
        input.files = files;
        previewImage({ target: input });
    }
});

// Jalali Date Picker
const persianMonths = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
const persianWeekdays = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];

let currentJalaliYear, currentJalaliMonth;
const today = new Date();
const todayJalali = gregorianToJalali(today.getFullYear(), today.getMonth() + 1, today.getDate());
currentJalaliYear = todayJalali[0];
currentJalaliMonth = todayJalali[1];

document.getElementById('jalaliDateInput')?.addEventListener('click', function(e) {
    e.stopPropagation();
    const datepicker = document.getElementById('persianDatepicker');
    datepicker.style.display = datepicker.style.display === 'none' ? 'block' : 'none';
    if (datepicker.style.display === 'block') {
        renderCalendar();
    }
});

document.addEventListener('click', function(e) {
    const datepicker = document.getElementById('persianDatepicker');
    const input = document.getElementById('jalaliDateInput');
    if (datepicker && !datepicker.contains(e.target) && e.target !== input) {
        datepicker.style.display = 'none';
    }
});

function renderCalendar() {
    const datepicker = document.getElementById('persianDatepicker');
    const daysInMonth = currentJalaliMonth <= 6 ? 31 : (currentJalaliMonth <= 11 ? 30 : (isJalaliLeapYear(currentJalaliYear) ? 30 : 29));
    const firstDayOfMonth = getJalaliDayOfWeek(currentJalaliYear, currentJalaliMonth, 1);
    
    let html = `
        <div class="header">
            <button type="button" onclick="changeMonth(1); event.stopPropagation();">◀</button>
            <span style="font-weight: 600;">${persianMonths[currentJalaliMonth - 1]} ${toPersianNumber(currentJalaliYear)}</span>
            <button type="button" onclick="changeMonth(-1); event.stopPropagation();">▶</button>
        </div>
        <div class="weekdays">
            ${persianWeekdays.map(day => `<div class="weekday">${day}</div>`).join('')}
        </div>
        <div class="days">
    `;
    
    // Empty cells before first day
    for (let i = 0; i < firstDayOfMonth; i++) {
        html += '<div class="day other-month"></div>';
    }
    
    // Days of month
    for (let day = 1; day <= daysInMonth; day++) {
        const isToday = day === todayJalali[2] && currentJalaliMonth === todayJalali[1] && currentJalaliYear === todayJalali[0];
        const classes = ['day'];
        if (isToday) classes.push('today');
        
        html += `<div class="${classes.join(' ')}" onclick="selectDate(${day}); event.stopPropagation();">${toPersianNumber(day)}</div>`;
    }
    
    html += '</div></div>';
    datepicker.innerHTML = html;
}

function changeMonth(direction) {
    currentJalaliMonth += direction;
    if (currentJalaliMonth > 12) {
        currentJalaliMonth = 1;
        currentJalaliYear++;
    } else if (currentJalaliMonth < 1) {
        currentJalaliMonth = 12;
        currentJalaliYear--;
    }
    renderCalendar();
}

function selectDate(day) {
    const jalaliDate = `${currentJalaliYear}/${String(currentJalaliMonth).padStart(2, '0')}/${String(day).padStart(2, '0')}`;
    const persianDate = toPersianNumber(jalaliDate);
    document.getElementById('jalaliDateInput').value = persianDate;
    
    const gregorianDate = jalaliToGregorian(currentJalaliYear, currentJalaliMonth, day);
    document.getElementById('gregorianDateInput').value = gregorianDate;
    document.getElementById('persianDatepicker').style.display = 'none';
}

function getJalaliDayOfWeek(jy, jm, jd) {
    const g = jalaliToGregorianArray(jy, jm, jd);
    const date = new Date(g[0], g[1] - 1, g[2]);
    return (date.getDay() + 1) % 7; // Adjust: Saturday = 0
}

function isJalaliLeapYear(year) {
    const breaks = [1, 5, 9, 13, 17, 22, 26, 30];
    const gy = year + 621;
    let jp = breaks[0];
    let jump = 0;
    
    for (let i = 1; i < breaks.length; i++) {
        const jm = breaks[i];
        jump = jm - jp;
        if (year < jm) break;
        jp = jm;
    }
    
    let n = year - jp;
    if (jump - n < 6) n = n - jump + (((jump + 4) - n) % 33);
    
    return ((n + 1) % 33) - 1 === 0 || (n % 33) - 1 === 0;
}

function toPersianNumber(num) {
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return String(num).replace(/\d/g, x => persianDigits[x]);
}

function gregorianToJalali(gy, gm, gd) {
    const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    let jy = (gy <= 1600) ? 0 : 979;
    gy -= (gy <= 1600) ? 621 : 1600;
    let days = (365 * gy) + (Math.floor((gy + 3) / 4)) - (Math.floor((gy + 99) / 100)) + (Math.floor((gy + 399) / 400)) - 80 + gd + g_d_m[gm - 1];
    if (gm > 2 && ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0))) days++;
    
    jy = jy + 33 * (Math.floor(days / 12053));
    days %= 12053;
    
    jy = jy + (4 * (Math.floor(days / 1461)));
    days %= 1461;
    
    if (days > 365) {
        jy = jy + Math.floor((days - 1) / 365);
        days = (days - 1) % 365;
    }
    
    const jm = (days < 186) ? 1 + Math.floor(days / 31) : 7 + Math.floor((days - 186) / 30);
    const jd = 1 + ((days < 186) ? (days % 31) : ((days - 186) % 30));
    
    return [jy, jm, jd];
}

function jalaliToGregorianArray(jy, jm, jd) {
    const g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    const j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
    
    jy -= 979;
    jm -= 1;
    jd -= 1;
    
    let j_day_no = 365 * jy + Math.floor(jy / 33) * 8 + Math.floor((jy % 33 + 3) / 4);
    for (let i = 0; i < jm; ++i) j_day_no += j_days_in_month[i];
    j_day_no += jd;
    
    let g_day_no = j_day_no + 79;
    let gy = 1600 + 400 * Math.floor(g_day_no / 146097);
    g_day_no %= 146097;
    
    let leap = true;
    if (g_day_no >= 36525) {
        g_day_no--;
        gy += 100 * Math.floor(g_day_no / 36524);
        g_day_no %= 36524;
        if (g_day_no >= 365) g_day_no++;
        leap = false;
    }
    
    gy += 4 * Math.floor(g_day_no / 1461);
    g_day_no %= 1461;
    
    if (g_day_no >= 366) {
        leap = false;
        g_day_no--;
        gy += Math.floor(g_day_no / 365);
        g_day_no = g_day_no % 365;
    }
    
    let gm = 0;
    for (let i = 0; g_day_no >= g_days_in_month[i] + (i === 1 && leap ? 1 : 0); i++) {
        g_day_no -= g_days_in_month[i] + (i === 1 && leap ? 1 : 0);
        gm++;
    }
    const gd = g_day_no + 1;
    
    return [gy, gm + 1, gd];
}

function jalaliToGregorian(jy, jm, jd) {
    const g = jalaliToGregorianArray(jy, jm, jd);
    return `${g[0]}-${String(g[1]).padStart(2, '0')}-${String(g[2]).padStart(2, '0')}`;
}
</script>

<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
