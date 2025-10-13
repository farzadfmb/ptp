// Persian Calendar (Jalali/Shamsi) Implementation
// Accurate conversion between Gregorian and Persian dates

class PersianCalendar {
    constructor() {
        this.monthNames = [
            'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
            'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
        ];
        
        this.dayNames = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
        
        // Current Persian date
        const today = new Date();
        const persianDate = this.gregorianToPersian(today.getFullYear(), today.getMonth() + 1, today.getDate());
        this.currentYear = persianDate.year;
        this.currentMonth = persianDate.month - 1; // Make it 0-indexed
        this.currentDay = persianDate.day;
        
        // Display date (can be different from current for navigation)
        this.displayYear = this.currentYear;
        this.displayMonth = this.currentMonth;
    }
    
    // Convert Gregorian date to Persian - Accurate Algorithm
    gregorianToPersian(gy, gm, gd) {
        let jy, jm, jd;
        
        if (gy <= 1600) {
            jy = 0;
            gy -= 621;
        } else {
            jy = 979;
            gy -= 1600;
        }
        
        let gy2 = (gm > 2) ? (gy + 1) : gy;
        let days = (365 * gy) + (Math.floor((gy2 + 3) / 4)) + (Math.floor((gy2 + 99) / 100)) + 
                  (Math.floor((gy2 + 399) / 400)) - 80 + gd + 
                  ((gm < 3) ? 0 : Math.floor((gm - 1) * 30.6001));
        
        jy += 33 * Math.floor(days / 12053);
        days %= 12053;
        
        jy += 4 * Math.floor(days / 1461);
        days %= 1461;
        
        if (days >= 366) {
            jy += Math.floor((days - 1) / 365);
            days = (days - 1) % 365;
        }
        
        if (days < 186) {
            jm = 1 + Math.floor(days / 31);
            jd = 1 + (days % 31);
        } else {
            jm = 7 + Math.floor((days - 186) / 30);
            jd = 1 + ((days - 186) % 30);
        }
        
        return { year: jy, month: jm, day: jd };
    }
    
    // Simplified conversion for current use
    gregorianToPersianSimple(date) {
        // Using a more straightforward approach
        const baseYear = 1979;
        const baseMonth = 2; // March
        const baseDay = 21;
        
        const currentYear = date.getFullYear();
        const currentMonth = date.getMonth() + 1;
        const currentDay = date.getDate();
        
        // Calculate approximate Persian year
        let persianYear = currentYear - 621;
        if (currentMonth < 3 || (currentMonth === 3 && currentDay < 21)) {
            persianYear--;
        }
        
        // Determine Persian month and day based on Gregorian date
        let persianMonth, persianDay;
        
        const monthStart = [
            [3, 21], [4, 21], [5, 22], [6, 22], [7, 23], [8, 23], // فروردین تا شهریور
            [9, 23], [10, 23], [11, 22], [12, 22], [1, 20], [2, 19]  // مهر تا اسفند
        ];
        
        for (let i = 0; i < 12; i++) {
            const [gMonth, gDay] = monthStart[i];
            const nextIndex = (i + 1) % 12;
            const [nextGMonth, nextGDay] = monthStart[nextIndex];
            
            let currentMonthStart = new Date(currentYear, gMonth - 1, gDay);
            let nextMonthStart = new Date(currentYear, nextGMonth - 1, nextGDay);
            
            if (nextIndex < i) { // Next month is in next year
                nextMonthStart = new Date(currentYear + 1, nextGMonth - 1, nextGDay);
            }
            
            if (date >= currentMonthStart && date < nextMonthStart) {
                persianMonth = i;
                persianDay = Math.floor((date - currentMonthStart) / (24 * 60 * 60 * 1000)) + 1;
                break;
            }
        }
        
        return { year: persianYear, month: persianMonth, day: persianDay };
    }
    
    // Get number of days in Persian month
    getDaysInPersianMonth(year, month) {
        if (month < 6) {
            return 31; // فروردین تا شهریور
        } else if (month < 11) {
            return 30; // مهر تا بهمن
        } else {
            // اسفند - check for leap year
            return this.isPersianLeapYear(year) ? 30 : 29;
        }
    }
    
    // Check if Persian year is leap year
    isPersianLeapYear(year) {
        const breaks = [
            -14, 3, 7, 10, 16, 24, 33, 37, 40, 47, 53, 56, 64, 69, 72, 80, 84, 87, 95,
            100, 103, 111, 115, 118, 126, 130, 133, 141, 146, 149, 157, 161, 164, 172,
            177, 180, 188, 192, 195, 203, 208, 211, 219, 223, 226, 234, 239, 242, 250,
            254, 257, 265, 270, 273, 281, 285, 288, 296, 300, 303, 311, 316, 319, 327,
            331, 334, 342, 347, 350, 358, 362, 365, 373, 377, 380, 388, 393, 396, 404,
            408, 411, 419, 424, 427, 435, 439, 442, 450, 454, 457, 465, 470, 473, 481,
            485, 488, 496, 500, 503, 511, 516, 519, 527, 531, 534, 542, 546, 549, 557,
            562, 565, 573, 577, 580, 588, 592, 595, 603, 608, 611, 619, 623, 626, 634,
            638, 641, 649, 654, 657, 665, 669, 672, 680, 684, 687, 695, 700, 703, 711,
            715, 718, 726, 730, 733, 741, 746, 749, 757, 761, 764, 772, 776, 779, 787,
            792, 795, 803, 807, 810, 818, 823, 826, 834, 838, 841, 849, 853, 856, 864,
            869, 872, 880, 884, 887, 895, 899, 902, 910, 915, 918, 926, 930, 933, 941,
            945, 948, 956, 961, 964, 972, 976, 979, 987, 991, 994, 1002, 1007, 1010,
            1018, 1022, 1025, 1033, 1037, 1040, 1048, 1053, 1056, 1064, 1068, 1071,
            1079, 1083, 1086, 1094, 1099, 1102, 1110, 1114, 1117, 1125, 1129, 1132,
            1140, 1145, 1148, 1156, 1160, 1163, 1171, 1175, 1178, 1186, 1191, 1194,
            1202, 1206, 1209, 1217, 1221, 1224, 1232, 1237, 1240, 1248, 1252, 1255,
            1263, 1267, 1270, 1278, 1283, 1286, 1294, 1298, 1301, 1309, 1314, 1317,
            1325, 1329, 1332, 1340, 1344, 1347, 1355, 1360, 1363, 1371, 1375, 1378,
            1386, 1391, 1394, 1402, 1406, 1409, 1417, 1421, 1424, 1432, 1437, 1440,
            1448, 1452, 1455, 1463, 1467, 1470, 1478, 1483, 1486, 1494, 1498, 1501,
            1509, 1513, 1516, 1524, 1529, 1532, 1540, 1544, 1547, 1555, 1560, 1563,
            1571, 1575, 1578, 1586, 1590, 1593, 1601, 1606, 1609, 1617, 1621, 1624,
            1632, 1636, 1639, 1647, 1652, 1655, 1663, 1667, 1670, 1678, 1682, 1685,
            1693, 1698, 1701, 1709, 1713, 1716, 1724, 1728, 1731, 1739, 1744, 1747,
            1755, 1759, 1762, 1770, 1774, 1777, 1785, 1790, 1793, 1801, 1805, 1808,
            1816, 1820, 1823, 1831, 1836, 1839, 1847, 1851, 1854, 1862, 1866, 1869,
            1877, 1882, 1885, 1893, 1897, 1900, 1908, 1912, 1915, 1923, 1928, 1931,
            1939, 1943, 1946, 1954, 1958, 1961, 1969, 1974, 1977, 1985, 1989, 1992,
            2000, 2004, 2007, 2015, 2020, 2023, 2031, 2035, 2038, 2046, 2050, 2053,
            2061, 2066, 2069, 2077, 2081, 2084, 2092, 2096, 2099, 2107, 2112, 2115,
            2123, 2127, 2130, 2138, 2142, 2145, 2153, 2158, 2161, 2169, 2173, 2176,
            2184, 2188, 2191, 2199, 2204, 2207, 2215, 2219, 2222, 2230, 2234, 2237,
            2245, 2250, 2253, 2261, 2265, 2268, 2276, 2280, 2283, 2291, 2296, 2299,
            2307, 2311, 2314, 2322, 2326, 2329, 2337, 2342, 2345, 2353, 2357, 2360,
            2368, 2372, 2375, 2383, 2388, 2391, 2399, 2403, 2406, 2414, 2418, 2421,
            2429, 2434, 2437, 2445, 2449, 2452, 2460, 2464, 2467, 2475, 2480, 2483,
            2491, 2495, 2498, 2506, 2510, 2513, 2521, 2526, 2529, 2537, 2541, 2544,
            2552, 2556, 2559, 2567, 2572, 2575, 2583, 2587, 2590, 2598, 2602, 2605,
            2613, 2618, 2621, 2629, 2633, 2636, 2644, 2648, 2651, 2659, 2664, 2667,
            2675, 2679, 2682, 2690, 2694, 2697, 2705, 2710, 2713, 2721, 2725, 2728,
            2736, 2740, 2743, 2751, 2756, 2759, 2767, 2771, 2774, 2782, 2786, 2789,
            2797, 2802, 2805, 2813, 2817, 2820, 2828, 2832, 2835, 2843, 2848, 2851,
            2859, 2863, 2866, 2874, 2878, 2881, 2889, 2894, 2897, 2905, 2909, 2912,
            2920, 2924, 2927, 2935, 2940, 2943, 2951, 2955, 2958, 2966, 2970, 2973,
            2981, 2986, 2989, 2997, 3001, 3004, 3012, 3016, 3019, 3027, 3032, 3035,
            3043, 3047, 3050, 3058, 3062, 3065, 3073, 3078, 3081, 3089, 3093, 3096,
            3104, 3108, 3111, 3119, 3124, 3127, 3135, 3139, 3142, 3150, 3154, 3157,
            3165, 3170, 3173, 3181, 3185, 3188, 3196, 3200, 3203, 3211, 3216, 3219,
            3227, 3231, 3234, 3242, 3246, 3249, 3257, 3262, 3265, 3273, 3277, 3280,
            3288, 3292, 3295, 3303, 3308, 3311, 3319, 3323, 3326, 3334, 3338, 3341,
            3349, 3354, 3357, 3365, 3369, 3372, 3380, 3384, 3387, 3395, 3400, 3403,
            3411, 3415, 3418, 3426, 3430, 3433, 3441, 3446, 3449, 3457, 3461, 3464,
            3472, 3476, 3479, 3487, 3492, 3495, 3503, 3507, 3510, 3518, 3522, 3525,
            3533, 3538, 3541, 3549, 3553, 3556, 3564, 3568, 3571, 3579, 3584, 3587,
            3595, 3599, 3602, 3610, 3614, 3617, 3625, 3630, 3633, 3641, 3645, 3648,
            3656, 3660, 3663, 3671, 3676, 3679, 3687, 3691, 3694, 3702, 3706, 3709,
            3717, 3722, 3725, 3733, 3737, 3740, 3748, 3752, 3755, 3763, 3768, 3771,
            3779, 3783, 3786, 3794, 3798, 3801
        ];
        
        const cycleStart = year - ((year >= 0) ? 474 : 473);
        const cycleYear = cycleStart % 2820 + 474;
        
        let aux1, aux2;
        if (cycleYear < 682) {
            aux1 = 0;
            aux2 = cycleYear;
        } else {
            aux1 = Math.floor((cycleYear - 682) / 128);
            aux2 = (cycleYear - 682) % 128;
        }
        
        if (aux2 < 29) {
            let jump = 0;
        } else {
            let jump = Math.floor(aux2 / 33);
            aux2 = aux2 % 33;
        }
        
        let leap = ((aux2 + 3) / 4);
        if (aux2 % 4 == 0 && aux2 != 0) {
            return true;
        }
        
        // Simplified leap year calculation
        const cycle = year % 128;
        const leapYears = [1, 5, 9, 13, 17, 22, 26, 30, 34, 38, 42, 46, 50, 55, 59, 63, 67, 71, 75, 79, 83, 88, 92, 96, 100, 104, 108, 112, 116, 121, 125];
        return leapYears.includes(cycle);
    }
    
    // Get first day of week for Persian month (0 = Saturday, 6 = Friday)
    getFirstDayOfPersianMonth(year, month) {
        // Convert first day of Persian month to Gregorian
        const gregorianDate = this.persianToGregorian(year, month + 1, 1);
        // Get day of week (0 = Sunday, 6 = Saturday)
        const gregDay = gregorianDate.getDay();
        // Convert to Persian week (0 = Saturday, 6 = Friday)
        return (gregDay + 1) % 7;
    }
    
    // Convert Persian date to Gregorian - Accurate Algorithm
    persianToGregorian(jy, jm, jd) {
        let gy, gm, gd;
        
        if (jy <= 979) {
            gy = 1600;
            jy -= 979;
        } else {
            gy = 621;
            jy -= 0;
        }
        
        if (jm < 1) {
            jy--;
            jm += 12;
        }
        
        let jp = 0;
        for (let i = 0; i < jm - 1; ++i) {
            jp += this.getDaysInPersianMonth(jy, i);
        }
        jp += jd - 1;
        
        let jp_g = jp + 79;
        
        gy += 33 * Math.floor(jp_g / 12053);
        jp_g %= 12053;
        
        gy += 4 * Math.floor(jp_g / 1461);
        jp_g %= 1461;
        
        if (jp_g >= 366) {
            gy += Math.floor((jp_g - 1) / 365);
            jp_g = (jp_g - 1) % 365;
        }
        
        if (jp_g < 186) {
            gm = 1 + Math.floor(jp_g / 31);
            gd = 1 + (jp_g % 31);
        } else {
            gm = 7 + Math.floor((jp_g - 186) / 30);
            gd = 1 + ((jp_g - 186) % 30);
        }
        
        if (gm > 12) {
            gy++;
            gm -= 12;
        }
        
        return new Date(gy, gm - 1, gd);
    }
}

// Initialize Persian Calendar
const persianCalendar = new PersianCalendar();

// Calendar Display Elements
let display = document.querySelector(".display");
let days = document.querySelector(".days");
let previous = document.querySelector(".left");
let next = document.querySelector(".right");

function displayPersianCalendar() {
    const year = persianCalendar.displayYear;
    const month = persianCalendar.displayMonth;
    
    // Clear previous days
    days.innerHTML = "";
    
    // Display month and year
    display.innerHTML = `${persianCalendar.monthNames[month]} ${year}`;
    
    // Get first day of month
    const firstDayOfWeek = persianCalendar.getFirstDayOfPersianMonth(year, month);
    
    // Get number of days in month
    const daysInMonth = persianCalendar.getDaysInPersianMonth(year, month);
    
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
        if (year === persianCalendar.currentYear && 
            month === persianCalendar.currentMonth && 
            day === persianCalendar.currentDay) {
            div.classList.add("current-date");
        }
        
        days.appendChild(div);
    }
}

// Convert numbers to Farsi
function convertToFarsiNumbers(num) {
    const farsiDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return num.toString().replace(/\d/g, (digit) => farsiDigits[parseInt(digit)]);
}

// Navigation event listeners
previous.addEventListener("click", () => {
    persianCalendar.displayMonth--;
    if (persianCalendar.displayMonth < 0) {
        persianCalendar.displayMonth = 11;
        persianCalendar.displayYear--;
    }
    displayPersianCalendar();
});

next.addEventListener("click", () => {
    persianCalendar.displayMonth++;
    if (persianCalendar.displayMonth > 11) {
        persianCalendar.displayMonth = 0;
        persianCalendar.displayYear++;
    }
    displayPersianCalendar();
});

// Initialize calendar display
displayPersianCalendar();