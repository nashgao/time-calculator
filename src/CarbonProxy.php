<?php

declare(strict_types=1);

namespace Nashgao\TimeCalculator;

use Carbon\Carbon;

class CarbonProxy extends Carbon
{
    const SEC_IN_DAY = 86400;

    /**
     * indicates the number of seconds passed before entering the 'day time'
     * can be overwritten since it's called in functions with static.
     */
    public static int $day = 21600;

    public static int $day_hour = 6;
    /**
     * indicates the number of seconds passed before entering the 'night time'
     * can be overwritten since it's called in functions with static.
     */
    public static int $night = 64800;

    public static int $night_hour = 18;

    /**
     * timezone offset
     * @var int|float
     */
    public static int $offset = 10 * 3600;

    public static string $timezone = 'Australia/Queensland';

    public static function now($tz = null): Carbon
    {
        return Carbon::now($tz ?? static::$timezone);
    }

    public static function parse($time = null, $tz = null): Carbon
    {
        if (! is_string($time)) {
            return Carbon::createFromTimestamp($time, $tz ?? static::$timezone);
        }
        return Carbon::parse($time, $tz);
    }

    /**
     * if the current time is between 6am to 6pm ().
     */
    public static function day($value = null, string $tz = null): bool
    {
        $tz = $tz ?? static::$timezone;
        if (! isset($value)) {
            return Carbon::now($tz ?? static::$timezone)->between(
                Carbon::createFromTime(static::$day_hour, 0, 0, $tz),
                Carbon::createFromTime(static::$night_hour, 0, 0, $tz)
            );
        }

        $parsed = static::parse($value, $tz);

        // if it's not today
        if (! $parsed->isToday()) {
            return $parsed->hour >= 6 and $parsed->hour <= 18;
        }

        return static::parse($value, $tz)->between(
            Carbon::createFromTime(static::$day_hour, 0, 0, $tz),
            Carbon::createFromTime(static::$night_hour, 0, 0, $tz)
        );
    }

    /**
     * if the current time is between 8pm to 6am (for ubertooth).
     */
    public static function night(int $value = null, string $tz = null): bool
    {
        return ! static::day($value, $tz ?? static::$timezone);
    }

    public static function timeRangeOverNight(string $starts, string $ends = null): bool
    {
        return static::timeRangeNightNum($starts, $ends) !== 0;
    }

    public static function timeRangeNightNum(string $starts, string $ends = null): int
    {
        $ends = isset($ends) ? static::parse($ends) : static::now();
        $starts = static::parse($starts);

        // check if two time stamps are in same date
        if ($starts->isSameDay($ends)) {
            return 0;
        }

        // if not same day (case can be like yesterday 11 pm and today 2 am)
        return intval(($ends->unix() + static::$offset) / static::SEC_IN_DAY) - intval(($starts->unix() + static::$offset) / static::SEC_IN_DAY);
    }

    public static function unixTimestampToday($tz = null): int
    {
        return Carbon::createFromTime(0, 0, 0, $tz ?? static::$timezone)->unix();
    }
}
