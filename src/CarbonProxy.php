<?php

declare(strict_types=1);

namespace Nashgao\TimeCalculator;

use Carbon\Carbon;

class CarbonProxy extends Carbon
{
    const SEC_IN_DAY = 86400;

    const SEC_6PM = 64800;

    const SEC_6AM = 21600;

    const AEST_OFFSET = 10 * 3600;

    public static function now($timezone = 'Australia/Queensland'): Carbon
    {
        return Carbon::now($timezone);
    }

    public static function parse($time = null, $timezone = 'Australia/Queensland'): Carbon
    {
        if (! is_string($time)) {

            return Carbon::createFromTimestamp($time, $timezone);
        }
        return Carbon::parse($time, $timezone);
    }

    /**
     * if the current time is between 6am to 6pm ().
     * @param null|int $time
     * @param string $timezone
     * @return bool
     */
    public static function day($time = null, string $timezone = 'Australia/Queensland'): bool
    {
        if (! isset($time)) {
            return Carbon::now($timezone)->between(
                Carbon::createFromTime(6, 0, 0, $timezone),
                Carbon::createFromTime(18, 0, 0, $timezone)
            );
        }

        $parsed = static::parse($time, $timezone);

        // if it's not today
        if (! $parsed->isToday()) {
            return $parsed->hour >= 6 and $parsed->hour <= 18;
        }

        return static::parse($time, $timezone)->between(
            Carbon::createFromTime(6, 0, 0, $timezone),
            Carbon::createFromTime(18, 0, 0, $timezone)
        );
    }

    /**
     * if the current time is between 8pm to 6am (for ubertooth).
     * @param int|null $time
     * @param string $timezone
     * @return bool
     */
    public static function night(int $time = null, string $timezone = 'Australia/Queensland'): bool
    {
        return ! static::day($time, $timezone);
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
        return intval(($ends->unix() + static::AEST_OFFSET) / static::SEC_IN_DAY) - intval(($starts->unix() + static::AEST_OFFSET) / static::SEC_IN_DAY);
    }

    public static function unixTimestampToday($timezone = 'Australia/Queensland'): int
    {
        return Carbon::createFromTime(0, 0, 0, $timezone)->unix();
    }
}
