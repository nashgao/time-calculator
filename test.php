<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Nashgao\TimeCalculator\CarbonProxy;
use Nashgao\TimeCalculator\Calculator;

//$calculator = new Calculator();
//$startTime = CarbonProxy::parse('2000-01-01 02:00:00');
//$endTime   = CarbonProxy::parse('2000-01-01 03:00:00');
//[$dayTime, $nightTime] = $calculator->process($startTime, $endTime);
//var_dump(['day' => $dayTime, 'night' => $nightTime]);

$calculator = new Calculator();
$startTime = CarbonProxy::parse('2000-01-01 01:00:00');
$endTime = CarbonProxy::parse('2000-01-01 23:00:00');
[$dayTime, $nightTime] = $calculator->process($startTime, $endTime);
var_dump($dayTime, $nightTime);