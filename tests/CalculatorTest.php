<?php

use Kalani\DateRange\Calculator;
use Carbon\Carbon;


class CalculatorTest extends PHPUnit_Framework_TestCase
{
    protected function setupCalculator(
        $strStart = '', $strEnd = ''
    ) {
        $start = $strStart ? new Carbon($strStart) : '';
        $end = $strEnd ? new Carbon($strEnd) : '';
        return new Calculator($start, $end);
    }

    /**
     * @test
     */
    public function it_calculates_hours()
    {
        $test = $this->setupCalculator('10:00:00','14:00:00');

        $this->assertEquals(4, $test->hours());
    }

    /**
     * @test
     */
    public function it_can_round_hours()
    {
        $test = $this->setupCalculator('10:00:00','14:12:00');

        $this->assertEquals(4.25, $test->hoursRoundedToNearest(15));
    }

    /**
     * @test
     */
    public function it_rounds_hours_by_default()
    {
        $test = $this->setupCalculator('10:00:00','14:12:00');

        $this->assertEquals(4.25, $test->hours(15));
    }

    /**
     * @test
     */
    public function it_can_calculate_difference_in_days()
    {
        $test = $this->setupCalculator(
            '2016-01-01 10:00:00','2016-01-04 14:12:00'
        );

        $this->assertEquals(3, $test->days());
    }

    /**
     * @test
     * @dataProvider getMonthData
     */
    public function it_rounds_months_to_nearest_2_weeks(
        $expected, $end
    ){
        $test = $this->setupCalculator(
            '2016-01-01 10:00:00',$end
        );

        $this->assertEquals($expected, $test->months());
    }

    public function getMonthData()
    {
        return array(
            [1, '2016-02-01'],
            [2, '2016-02-16 14:00:00'],
            [1, '2016-02-15'],
            [2, '2016-02-16'],
        );
    }

    /**
     * @test
     * @dataProvider getDecimalTimes
     */
    public function it_can_return_decimal_value_for_time(
        $expected, $time
    ) {
        $test = $this->setupCalculator($time);

        $this->assertEquals($expected, $test->decimal());
    }

    public function getDecimalTimes()
    {
        return array(
            [10.0, '10:00:00'],
            [10.5, '10:30:00'],
            [10.3, '10:15:00'],
            [10.2, '10:14:00'],
            [22.2, '10:14:00 pm'],
        );
    }

    /**
     * @test
     * @dataProvider getMissingPieces
     */
    public function it_returns_0_if_missing_piece(
        $method, $start, $end
    ) {
        $test = $this->setupCalculator($start, $end);

        $this->assertEquals(0, $test->$method());
    }

    public function getMissingPieces()
    {
        return array(
            ['hours', '', '14:00:00'],
            ['hours', '1:00:00', ''],
            ['hours', '', ''],
            ['days', '', '2016-01-01'],
            ['days', '2016-01-01', ''],
            ['days', '', ''],
            ['months', '', '2016-01-01'],
            ['months', '2016-01-01', ''],
            ['months', '', ''],
            ['decimal', '', ''],
            ['decimal', '', '10:00:00'],
        );
    }

}
