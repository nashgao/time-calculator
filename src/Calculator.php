<?php

declare(strict_types=1);

namespace Nashgao\TimeCalculator;

use Carbon\Carbon;


class Calculator
{
    /**
     * @param Carbon $startTime
     * @param Carbon $endTime
     * @return array[daytime, nighttime]
     */
    public static function process(Carbon $startTime, Carbon $endTime): array
    {
        $timeDiffInSec = $endTime->diffInSeconds($startTime);
        $days = intdiv($timeDiffInSec, CarbonProxy::SEC_IN_DAY);

        $overnight = $days > 0
                    ? CarbonProxy::timeRangeOverNight($startTime->addDays($days)->toDateTimeString(), $endTime->toDateTimeString())
                    : CarbonProxy::timeRangeOverNight($startTime->toDateTimeString(), $endTime->toDateTimeString());

        // then calculate the for the rest of the time, how much time was in day and night
        $timeRange = $overnight ? [$startTime, $endTime, true] : [$startTime, $endTime, false];
        $time = static::calculate($timeRange);

        return [
            $time[0] + $days * CarbonProxy::SEC_IN_DAY / 2, // day time
            $time[1] + $days * CarbonProxy::SEC_IN_DAY / 2, // night time
        ];
    }

    /**
     * @param array $timeRange
     * @return float[]|int[]|null[] [daytime, nighttime]
     */
    protected static function calculate(array $timeRange): array
    {
        $dayTime = null;
        $nightTime = null;

        /**
         * @var Carbon $startTime
         * @var Carbon $endTime
         * @var bool $overnight
         */
        [ $startTime, $endTime, $overnight ] = $timeRange;
        $todayUnix = CarbonProxy::unixTimestampToday();

        // if it's in the same day
        if (! $overnight) {
            $startsAt = $startTime->unix() - $todayUnix;
            $endsAt = $endTime->unix() - $todayUnix;

            while ($startsAt < 0) {
                $startsAt = CarbonProxy::SEC_IN_DAY + $startsAt;
            }

            while ($endsAt < 0) {
                $endsAt = CarbonProxy::SEC_IN_DAY + $endsAt;
            }

            // if start time is after 6pm or end time is before 6am
            if ($startsAt > CarbonProxy::SEC_6PM or $endsAt < CarbonProxy::SEC_6AM) {
                // that means it's all night time, day time is 0
                $dayTime = 0;
                $nightTime = $endsAt - $startsAt;
            } elseif ($startsAt > CarbonProxy::SEC_6AM and $endsAt < CarbonProxy::SEC_6PM) {
                // which means no night time, only day time
                $nightTime = 0;
                $dayTime = $endsAt - $startsAt;
            } else {
                // 6am in sec - start time, if it's negative then does not count
                $startTimeToSixAm = CarbonProxy::SEC_6AM - $startsAt;

                if ($startTimeToSixAm > 0) {
                    $nightTime = $nightTime + $startTimeToSixAm;
                }

                // then check 6pm - start time
                $sixPmToEndTime = $endsAt - CarbonProxy::SEC_6PM;
                if ($sixPmToEndTime > 0) {
                    $nightTime = $nightTime + $sixPmToEndTime;
                }

                $dayTime = $endsAt - $startsAt - $nightTime;
            }
            return [$dayTime, $nightTime];
        }

        // if it across 12pm
        // reverse starts at and ends at
        $endsAt = $startTime->addDay()->unix() - $todayUnix;
        $startsAt = $endTime->unix() - $todayUnix;

        while ($startsAt < 0) {
            $startsAt = CarbonProxy::SEC_IN_DAY + $startsAt;
        }

        while ($endsAt < 0) {
            $endsAt = CarbonProxy::SEC_IN_DAY + $endsAt;
        }

        // if start time is after 6pm or end time is before 6am
        if ($startsAt > CarbonProxy::SEC_6PM or $endsAt < CarbonProxy::SEC_6AM) {
            // that means it's all night time, day time is 0
            $dayTime = CarbonProxy::SEC_IN_DAY / 2;
            $nightTime = CarbonProxy::SEC_IN_DAY / 2 - ($endsAt - $startsAt);
        } elseif ($startsAt > CarbonProxy::SEC_6AM and $endsAt < CarbonProxy::SEC_6PM) {
            // which means no night time, only day time
            $nightTime = CarbonProxy::SEC_IN_DAY / 2;
            $dayTime = CarbonProxy::SEC_IN_DAY / 2 - ($endsAt - $startsAt);
        } else {
            // 6am in sec - start time, if it's negative then does not count
            $startTimeToSixAm = CarbonProxy::SEC_6AM - $startsAt;

            if ($startTimeToSixAm > 0) {
                $nightTime = $nightTime + $startTimeToSixAm;
            }

            // then check 6pm - start time
            $sixPmToEndTime = $endsAt - CarbonProxy::SEC_6PM;
            if ($sixPmToEndTime > 0) {
                $nightTime = $nightTime + $sixPmToEndTime;
            }

            $dayTime = $endsAt - $startsAt - $nightTime;
            $dayTime = CarbonProxy::SEC_IN_DAY / 2 - $dayTime;
            $nightTime = CarbonProxy::SEC_IN_DAY / 2 - $nightTime;
        }
        return [$dayTime, $nightTime];
    }
}
