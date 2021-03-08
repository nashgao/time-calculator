<?php

declare(strict_types=1);

namespace Nashgao\Test\Cases;

use Nashgao\TimeCalculator\Calculator;
use Nashgao\TimeCalculator\CarbonProxy;

class CalculatorTest extends AbstractTest
{
    public function testDayTime()
    {
        $calculator = new Calculator();
        $startTime = CarbonProxy::parse('2000-01-01 07:00:00');
        $endTime   = CarbonProxy::parse('2000-01-01 08:00:00');
        [$dayTime, $nightTime] = $calculator->process($startTime, $endTime);
        $this->assertEquals(60 * 60, $dayTime);
        $this->assertEquals(0, $nightTime);
    }

    public function testNightTime()
    {
        $calculator = new Calculator();
        $startTime = CarbonProxy::parse('2000-01-01 02:00:00');
        $endTime   = CarbonProxy::parse('2000-01-01 03:00:00');
        [$dayTime, $nightTime] = $calculator->process($startTime, $endTime);
        $this->assertEquals(0, $dayTime);
        $this->assertEquals(60 * 60, $nightTime);

        // test night time overnight
        $startTime = CarbonProxy::parse('2000-1-1 23:00:00');
        $endTime   = CarbonProxy::parse('2000-1-2 01:00:00');
        [$dayTime, $nightTime] = $calculator->process($startTime, $endTime);
        $this->assertEquals(0, $dayTime);
        $this->assertEquals(60 * 60 * 2, $nightTime);
    }

    public function testCrossDayNightWithSameDay()
    {
        $calculator = new Calculator();
        $startTime = CarbonProxy::parse('2000-01-01 01:00:00');
        $endTime = CarbonProxy::parse('2000-01-01 23:00:00');
        [$dayTime, $nightTime] = $calculator->process($startTime, $endTime);
        $this->assertEquals(60 * 60 * 12, $dayTime);
        $this->assertEquals(60 * 60 * 10, $nightTime);


        $startTime = CarbonProxy::parse('2000-01-01 10:00:00');
        $endTime = CarbonProxy::parse('2000-01-02 10:00:00');
        [$dayTime, $nightTime] = $calculator->process($startTime, $endTime);
        $this->assertEquals(60 * 60 * 12, $dayTime);
        $this->assertEquals(60 * 60 * 12, $nightTime);

        $startTime = CarbonProxy::parse('2000-01-01 10:00:00');
        $endTime = CarbonProxy::parse('2000-01-02 19:00:00');
        [$dayTime, $nightTime] = $calculator->process($startTime, $endTime);
        $this->assertEquals(60 * 60 * 20, $dayTime);
        $this->assertEquals(60 * 60 * 13, $nightTime);


        $startTime = CarbonProxy::parse('2000-01-01 19:00:00');
        $endTime = CarbonProxy::parse('2000-01-03 02:00:00');
        [$dayTime, $nightTime] = $calculator->process($startTime, $endTime);
        $this->assertEquals(60 * 60 * 12, $dayTime);
        $this->assertEquals(60 * 60 * 19, $nightTime);
    }
}
