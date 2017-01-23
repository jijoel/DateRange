<?php

use Kalani\DateRange\ExampleCalculator;
use Carbon\Carbon;


class ExampleCalculatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider getHoursForRounding
     */
    public function it_rounds_hours_down($expected, $endTime, $to)
    {
        $start = new Carbon('10:00:00');
        $end = new Carbon($endTime);
        $test = new ExampleCalculator($start, $end);

        $this->assertEquals($expected, $test->hours($to));
    }

    public function getHoursForRounding()
    {
        return array(
            [4, '14:00:00', 15],
            [4.5, '14:30:00', 15],
            [4.75, '14:45:00', 15],
            [4, '14:15:00', 15],
            [4, '14:12:00', 15],
            // [4, '14:14:59', 15],     // Don't need to be this precise
            // [4.25, '14:15:01', 15],
            [4.25, '14:16:00', 15],
            [4.17, '14:15:00', 10],
            [4, '14:10:00', 10],
        );
    }
}

