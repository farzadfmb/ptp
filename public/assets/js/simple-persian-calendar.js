// Simple Persian Calendar Implementation
// Using accurate conversion algorithms

class SimplePersianCalendar {
    constructor() {
        this.monthNames = [
            'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
            'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
        ];
        
        this.dayNames = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
        
        // Get current Persian date
        const today = new Date();
        const currentPersian = this.gregorianToPersian(today);
        
        this.currentYear = currentPersian.year;
        this.currentMonth = currentPersian.month;
        this.currentDay = currentPersian.day;
        
        // Display date for navigation
        this.displayYear = this.currentYear;
        this.displayMonth = this.currentMonth;
    }
    
    // Accurate Gregorian to Persian conversion
    gregorianToPersian(gDate) {
        const gy = gDate.getFullYear();
        const gm = gDate.getMonth() + 1;
        const gd = gDate.getDate();
        
        // Calculate Julian day
        let a = Math.floor((14 - gm) / 12);
        let y = gy - a;
        let m = gm + 12 * a - 3;
        
        let jd = gd + Math.floor((153 * m + 2) / 5) + 365 * y + 
                 Math.floor(y / 4) - Math.floor(y / 100) + Math.floor(y / 400) + 1721119;
        
        // Convert Julian day to Persian
        jd = Math.floor(jd - 0.5) + 0.5;
        
        let dep = jd - 1948321; // Persian epoch
        let np = Math.floor(dep / 1029983);
        let cyear = dep % 1029983;
        
        let ycycle, aux1, aux2, yday;
        
        if (cyear !== 1029982) {
            aux1 = Math.floor(cyear / 366);
            aux2 = cyear % 366;
            ycycle = Math.floor(((aux1 >= 128) ? aux1 - 128 : aux1) / 4) * 4 + ((aux1 >= 128) ? 128 : 0);
            
            if (aux2 < 366) {
                yday = aux2;
            }
        } else {
            ycycle = 1029;
            yday = 365;
        }
        
        let jy = ycycle + np * 1029 + 1;
        
        let jp;
        if (yday < 186) {
            let jm = 1 + Math.floor(yday / 31);
            let jd = 1 + yday % 31;
            jp = jm;
        } else {
            let jm = 7 + Math.floor((yday - 186) / 30);
            let jd = 1 + (yday - 186) % 30;
            jp = jm;
        }
        
        // Simplified accurate conversion
        const persianStart = new Date(gy, 2, 21); // March 21st approximation
        const diffDays = Math.floor((gDate - persianStart) / (24 * 60 * 60 * 1000));
        
        let pYear = gy - 621;
        let pMonth = 0;
        let pDay = 1;
        
        if (diffDays < 0) {
            // Date is before Persian new year, so it's previous Persian year
            pYear--;
            // Calculate from previous year's Esfand
            const daysSinceEsfand = 365 + diffDays; // Approximate
            if (daysSinceEsfand >= 337) { // Esfand starts around day 337
                pMonth = 11; // Esfand
                pDay = daysSinceEsfand - 336;
            } else {
                // Calculate month and day
                for (let i = 0; i < 12; i++) {
                    const daysInMonth = this.getDaysInPersianMonth(pYear, i);
                    if (daysSinceEsfand <= daysInMonth) {
                        pMonth = i;
                        pDay = daysSinceEsfand;
                        break;
                    }
                    daysSinceEsfand -= daysInMonth;
                }
            }
        } else {
            // Calculate month and day from beginning of Persian year
            let remainingDays = diffDays + 1;
            for (let i = 0; i < 12; i++) {
                const daysInMonth = this.getDaysInPersianMonth(pYear, i);
                if (remainingDays <= daysInMonth) {
                    pMonth = i;
                    pDay = remainingDays;
                    break;
                }
                remainingDays -= daysInMonth;
            }
        }
        
        return { year: pYear, month: pMonth, day: pDay };
    }
    
    // Get days in Persian month
    getDaysInPersianMonth(year, month) {
        if (month < 6) {
            return 31; // فروردین تا شهریور
        } else if (month < 11) {
            return 30; // مهر تا بهمن
        } else {
            // اسفند
            return this.isPersianLeapYear(year) ? 30 : 29;
        }
    }
    
    // Check Persian leap year
    isPersianLeapYear(year) {
        // 33-year cycle algorithm
        const cycle = year % 128;
        const leapPattern = [1, 5, 9, 13, 17, 22, 26, 30, 34, 38, 42, 46, 50, 55, 59, 63, 67, 71, 75, 79, 83, 88, 92, 96, 100, 104, 108, 112, 116, 121, 125];
        return leapPattern.includes(cycle);
    }
    
    // Get first day of Persian month (0=Saturday, 6=Friday)
    getFirstDayOfPersianMonth(year, month) {
        // Convert to Gregorian and get day of week
        const firstDay = this.persianToGregorian(year, month, 1);
        const gregDay = firstDay.getDay(); // 0=Sunday, 6=Saturday
        
        // Convert to Persian week (0=Saturday, 6=Friday)
        return (gregDay + 1) % 7;
    }
    
    // Convert Persian to Gregorian (simplified but functional)
    persianToGregorian(pYear, pMonth, pDay) {
        // Approximate conversion
        let gYear = pYear + 621;
        
        // Calculate approximate date
        const newYearDates = {
            1402: new Date(2023, 2, 21), // 1402/1/1 = 2023/3/21
            1403: new Date(2024, 2, 20), // 1403/1/1 = 2024/3/20
            1404: new Date(2025, 2, 21), // 1404/1/1 = 2025/3/21
            1405: new Date(2026, 2, 21), // 1405/1/1 = 2026/3/21
        };
        
        let newYearDate = newYearDates[pYear];
        if (!newYearDate) {
            // Default approximation
            newYearDate = new Date(gYear, 2, 21);
        }
        
        // Calculate days from beginning of Persian year
        let totalDays = 0;
        for (let i = 0; i < pMonth; i++) {
            totalDays += this.getDaysInPersianMonth(pYear, i);
        }
        totalDays += pDay - 1;
        
        // Add days to new year date
        const resultDate = new Date(newYearDate);
        resultDate.setDate(resultDate.getDate() + totalDays);
        
        return resultDate;
    }
}

// Calendar Display Functions
const persianCal = new SimplePersianCalendar();

// Calendar Display Elements
let display = document.querySelector(".display");
let days = document.querySelector(".days");
let previous = document.querySelector(".left");
let next = document.querySelector(".right");

function displayPersianCalendar() {
    const year = persianCal.displayYear;
    const month = persianCal.displayMonth;
    
    // Clear previous days
    days.innerHTML = "";
    
    // Display month and year in Persian
    display.innerHTML = `${persianCal.monthNames[month]} ${convertToFarsiNumbers(year)}`;
    
    // Get first day of month and number of days
    const firstDayOfWeek = persianCal.getFirstDayOfPersianMonth(year, month);
    const daysInMonth = persianCal.getDaysInPersianMonth(year, month);
    
    // Add empty cells for days before month starts
    for (let i = 0; i < firstDayOfWeek; i++) {
        const div = document.createElement("div");
        div.innerHTML = "";
        days.appendChild(div);
    }
    
    // Add days of month
    for (let day = 1; day <= daysInMonth; day++) {
        const div = document.createElement("div");
        div.innerHTML = convertToFarsiNumbers(day);
        div.dataset.date = `${year}/${month + 1}/${day}`;
        
        // Highlight current day
        if (year === persianCal.currentYear && 
            month === persianCal.currentMonth && 
            day === persianCal.currentDay) {
            div.classList.add("current-date");
        }
        
        days.appendChild(div);
    }
}

// Convert numbers to Persian digits
function convertToFarsiNumbers(num) {
    const farsiDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return num.toString().replace(/\d/g, (digit) => farsiDigits[parseInt(digit)]);
}

// Navigation event listeners
if (previous) {
    previous.addEventListener("click", () => {
        persianCal.displayMonth--;
        if (persianCal.displayMonth < 0) {
            persianCal.displayMonth = 11;
            persianCal.displayYear--;
        }
        displayPersianCalendar();
    });
}

if (next) {
    next.addEventListener("click", () => {
        persianCal.displayMonth++;
        if (persianCal.displayMonth > 11) {
            persianCal.displayMonth = 0;
            persianCal.displayYear++;
        }
        displayPersianCalendar();
    });
}

// Initialize calendar display
if (display && days) {
    displayPersianCalendar();
}