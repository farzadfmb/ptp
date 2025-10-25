<?php

class JalaliHelper
{
    /**
     * تبدیل تاریخ میلادی به شمسی
     */
    public static function toJalali($gregorianDate, $format = 'Y/m/d')
    {
        if (empty($gregorianDate)) {
            return '';
        }

        $timestamp = is_numeric($gregorianDate) ? $gregorianDate : strtotime($gregorianDate);
        
        if (!$timestamp) {
            return '';
        }

        list($jYear, $jMonth, $jDay) = self::gregorianToJalali(
            (int) date('Y', $timestamp),
            (int) date('m', $timestamp),
            (int) date('d', $timestamp)
        );

        $replacements = [
            'Y' => str_pad($jYear, 4, '0', STR_PAD_LEFT),
            'y' => substr(str_pad($jYear, 4, '0', STR_PAD_LEFT), 2),
            'm' => str_pad($jMonth, 2, '0', STR_PAD_LEFT),
            'n' => $jMonth,
            'd' => str_pad($jDay, 2, '0', STR_PAD_LEFT),
            'j' => $jDay,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $format);
    }

    /**
     * تبدیل تاریخ شمسی به میلادی
     */
    public static function toGregorian($jalaliDate)
    {
        if (empty($jalaliDate)) {
            return null;
        }

        // Parse different formats: 1403/06/14, 1403-06-14, etc.
        $jalaliDate = str_replace(['/', '-', ' '], '-', $jalaliDate);
        $parts = explode('-', $jalaliDate);

        if (count($parts) !== 3) {
            return null;
        }

        list($jYear, $jMonth, $jDay) = array_map('intval', $parts);

        if ($jYear < 1000) {
            $jYear += 1300; // تبدیل سال دو رقمی به چهار رقمی
        }

        list($gYear, $gMonth, $gDay) = self::jalaliToGregorian($jYear, $jMonth, $jDay);

        return sprintf('%04d-%02d-%02d', $gYear, $gMonth, $gDay);
    }

    /**
     * تبدیل تاریخ میلادی به شمسی (الگوریتم)
     */
    private static function gregorianToJalali($gYear, $gMonth, $gDay)
    {
        $gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

        $gy = $gYear - 1600;
        $gm = $gMonth - 1;
        $gd = $gDay - 1;

        $gDayNo = 365 * $gy + (int)(($gy + 3) / 4) - (int)(($gy + 99) / 100) + (int)(($gy + 399) / 400);

        for ($i = 0; $i < $gm; ++$i) {
            $gDayNo += $gDaysInMonth[$i];
        }

        if ($gm > 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0))) {
            $gDayNo++;
        }

        $gDayNo += $gd;

        $jDayNo = $gDayNo - 79;

        $jNp = (int)($jDayNo / 12053);
        $jDayNo %= 12053;

        $jy = 979 + 33 * $jNp + 4 * (int)($jDayNo / 1461);

        $jDayNo %= 1461;

        if ($jDayNo >= 366) {
            $jy += (int)(($jDayNo - 1) / 365);
            $jDayNo = ($jDayNo - 1) % 365;
        }

        $jm = 0;
        for ($i = 0; $i < 11 && $jDayNo >= $jDaysInMonth[$i]; ++$i) {
            $jDayNo -= $jDaysInMonth[$i];
            $jm++;
        }

        $jd = $jDayNo + 1;

        return [$jy, $jm + 1, $jd];
    }

    /**
     * تبدیل تاریخ شمسی به میلادی (الگوریتم)
     */
    private static function jalaliToGregorian($jYear, $jMonth, $jDay)
    {
        $gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

        $jy = $jYear - 979;
        $jm = $jMonth - 1;
        $jd = $jDay - 1;

        $jDayNo = 365 * $jy + (int)($jy / 33) * 8 + (int)(($jy % 33 + 3) / 4);
        
        for ($i = 0; $i < $jm; ++$i) {
            $jDayNo += $jDaysInMonth[$i];
        }

        $jDayNo += $jd;

        $gDayNo = $jDayNo + 79;

        $gy = 1600 + 400 * (int)($gDayNo / 146097);
        $gDayNo %= 146097;

        $leap = true;
        if ($gDayNo >= 36525) {
            $gDayNo--;
            $gy += 100 * (int)($gDayNo / 36524);
            $gDayNo %= 36524;

            if ($gDayNo >= 365) {
                $gDayNo++;
            }
            $leap = false;
        }

        $gy += 4 * (int)($gDayNo / 1461);
        $gDayNo %= 1461;

        if ($gDayNo >= 366) {
            $leap = false;

            $gDayNo--;
            $gy += (int)($gDayNo / 365);
            $gDayNo = $gDayNo % 365;
        }

        $gm = 0;
        for ($i = 0; $gDayNo >= $gDaysInMonth[$i] + ($i == 1 && $leap ? 1 : 0); $i++) {
            $gDayNo -= $gDaysInMonth[$i] + ($i == 1 && $leap ? 1 : 0);
            $gm++;
        }

        $gd = $gDayNo + 1;

        return [$gy, $gm + 1, $gd];
    }

    /**
     * نام ماه‌های شمسی
     */
    public static function getMonthName($month)
    {
        $months = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
        ];

        return $months[$month] ?? '';
    }
}
