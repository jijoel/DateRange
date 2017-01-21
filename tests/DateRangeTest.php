<?php

use Kalani\DateRange\DateRange;
use Carbon\Carbon;


class DateRangeTest extends PHPUnit_Framework_TestCase
{
    const DATE_ORDER_EXCEPTION = 'Kalani\DateRange\DateOrderException';

    const DATE_NOW = 'now';

    const DATE1_TINY  = '1/2/14';
    const DATE1_SHORT = '1/2/2014';
    const DATE1_LONG  = '2014-01-02 4:20pm';
    const DATE1_SQL   = '2014-01-02';
    const DATE1_TS    = '1388679600';

    const DATE2_TINY  = '1/5/14';
    const DATE2_SHORT = '1/5/2014';
    const DATE2_LONG  = '2014-01-05 4:20pm';
    const DATE2_SQL   = '2014-01-05';

    private $now;

    public function setUp()
    {
        parent::setUp();

        $this->now = Carbon::parse(self::DATE1_LONG);
        Carbon::setTestNow($this->now);

        $this->config = new MockConfig();
        $this->test = new DateRange($this->config);
    }

    public function tearDown()
    {
        Carbon::setTestNow();
        Mockery::close();
    }

    public function testExists(){}

    public function testShouldDefaultToNow()
    {
        $test = $this->test->make();

        $this->assertEquals($this->now, $test->start());
        $this->assertEquals($this->now, $test->end());

        $this->assertEquals($this->now, $test->start);
        $this->assertEquals($this->now, $test->end);        
    }

    public function testConstructorShouldAcceptString()
    {
        $string = self::DATE1_SHORT;
        $carbon = Carbon::parse($string);

        $test = $this->test->make($string);   // as a string
        $this->assertEquals($carbon, $test->start);

        $test = $this->test->make($carbon);  // as a Carbon object
        $this->assertEquals($carbon, $test->start);
    }

    public function testConstructorShouldAcceptTimestamp()
    {
        $carbon = Carbon::createFromTimestamp(self::DATE1_TS);

        $test = $this->test->make(self::DATE1_TS);
        $this->assertEquals($carbon, $test->start);
    }

    public function testConstructorShouldAcceptDateTimeObject()
    {
        $dt = new DateTime("2014-01-09 11:14:15.638276");
        $carbon = Carbon::instance($dt);

        $test = $this->test->make($dt);
        $this->assertEquals($carbon, $test->start);
    }

    public function testEndDateSetToStartDateByDefault()
    {
        $string = self::DATE1_SHORT;
        $carbon = Carbon::parse($string);

        $test = $this->test->make($string);   // as a string
        $this->assertEquals($carbon, $test->start);        
        $this->assertEquals($carbon, $test->end);        
    }

    public function testEndDateCanBeSetIndependently()
    {
        $start = self::DATE1_SHORT;
        $end = self::DATE2_SHORT;

        $test = $this->test->make(self::DATE1_TINY, $end);
        $this->assertEquals(Carbon::parse($start), $test->start);
        $this->assertEquals(Carbon::parse($end), $test->end);
    }

    public function testSwitchDatesIfEndPreceedsStart()
    {
        // $this->setExpectedException(self::DATE_ORDER_EXCEPTION);
        $test = $this->test->make(self::DATE2_SHORT, self::DATE1_SHORT);

        $this->assertEquals(Carbon::parse(self::DATE1_SHORT), $test->start);
    }

    public function testConstructorShouldAcceptNonApplicableValue()
    {
        $this->config->setup(['none.default'=>'n/a']);
        $test = $this->test->make(DateRange::NONE, Null);

        $this->assertEquals('n/a', $test->start);
        $this->assertEquals('n/a', $test->end);
    }

    public function testConstructorShouldAcceptNoneValue()
    {
        $this->config->setup(['none.default'=>'n/a']);
        $test = $this->test->make($this->test->none(), Null);

        $this->assertEquals('n/a', $test->start);
        $this->assertEquals('n/a', $test->end);
    }

    public function testShouldBeAbleToFormatDateWithNotationalString()
    {
        $this->config->setup(['styles.short'=>'x']);
        $test = $this->test->make(self::DATE1_TINY, Null);

        $this->assertEquals('x', $test->formatDate($test->start, 'short'));
    }

    public function testShouldFormatDateWithNoneValue()
    {
        $this->config->setup(['none.foo'=>'x']);
        $test = $this->test->make(DateRange::NONE);
        $this->assertEquals('x', $test->formatDate($test->start, 'foo'));
    }

    public function testShouldFormatDateWithDefaultStyle()
    {
        $this->config->setup(['styles.default'=>'x']);
        $test = $this->test->make(self::DATE1_TINY);
        $this->assertEquals('x', $test->formatDate($test->start, 'foo'));
    }

    public function testShouldFormatDateWithOverriddenDefaultStyle()
    {
        $this->config->setup(['styles.default'=>'z']);
        $test = $this->test->make(self::DATE1_TINY);
        $this->assertEquals('x', $test->formatDate($test->start, 'foo', 'x'));
    }

    public function testShouldFormatWithOverridenDefaultStyle()
    {
        $this->config->setup([
            'range.default' => ['only'=>''],
            'styles.default'=>'z'
        ]);
        $test = $this->test->make(self::DATE1_TINY);
        $this->assertEquals('x', $test->format('start', 'foo', 'x'));        
    }

    public function testShouldFormatEmptyDateWithOverridenDefaultValue()
    {
        $this->config->setup([
            'none.default' => 'n/a',
            'range.default' => ['only'=>''],
            'styles.default'=>'z'
        ]);
        $test = $this->test->make(DateRange::NONE);
        $this->assertEquals('value', $test->format('start', 'foo', Null, 'value'));
    }

    public function testShouldFormatRangeWithOverridenDefaultStyle()
    {
        $this->config->setup([
            'range.default' => ['only'=>''],
            'styles.default'=>'z']
        );
        $test = $this->test->make(self::DATE1_TINY);
        $this->assertEquals('x', $test->format('range', 'foo', 'x'));        
    }

    public function testShouldReturnSingleFormattedDate()
    {
        $this->config->setup(['styles.short'=>'n/j/Y']);
        $test = $this->test->make(self::DATE1_TINY);

        $this->assertEquals(self::DATE1_SHORT, $test->start_short);
    }

    public function testShouldReturnSingleFormattedDateWithMock()
    {
        $this->config->setup(['styles.short'=>'x']);
        $test = $this->test->make(self::DATE1_TINY);

        $this->assertEquals('x', $test->start_short);
    }

    public function testShouldReturnSingleFormattedDateFromRange()
    {
        $this->config->setup([
            'range.default'=>['only'=>'prefix '],
            'styles.short'=>'x'
        ]);
        $test = $this->test->make(self::DATE1_TINY,self::DATE1_SHORT);

        $this->assertEquals('prefix x', $test->range_short);
        $this->assertEquals('prefix x', $test->short);
    }

    public function testShouldReturnFormattedDateRange()
    {
        $this->config->setup([
            'range.default'=>['before'=>'a ','middle'=>' - ','after'=>' b'],
            'styles.short'=>'x'
        ]);
        $test = $this->test->make(self::DATE1_TINY,self::DATE2_TINY);
        $this->assertEquals('a x - x b', $test->range_short);
    }

    public function testShouldReturnFormattedDateWithTitle()
    {
        $this->config->setup([
            'range.title'=>['only'=>'Prefix '],
            'styles.short'=>'x'
        ]);
        $test = $this->test->make(self::DATE1_TINY);
        $this->assertEquals('Prefix x', $test->short_title);
    }

    public function testShouldReturnFormattedDateWithFullTitle()
    {
        $this->config->setup([
            'range.title'=>['only'=>'Prefix '],
            'styles.title'=>'X'
        ]);
        $test = $this->test->make(self::DATE1_TINY);
        $this->assertEquals('Prefix X', $test->title);
    }

    public function testCanReturnString()
    {
        $this->config->setup([
            'range.default'=>['only'=>''],
            'default'=>'X'
        ]);

        $test = $this->test->make(self::DATE1_TINY);

        $this->assertTrue(is_string((string)$test));
    }



    /**
     * @dataProvider getDatesForConstructor
     */
    public function testConstructorWithDates(
        $start, $end, $expectedStart, $expectedEnd
    ){
        $this->config->setup(['styles.sql'=>'Y-m-d']);
        $test = $this->test->make($start, $end);

        $this->assertEquals($expectedStart, $test->start_sql);
        $this->assertEquals($expectedEnd, $test->end_sql);
    }

    public function getDatesForConstructor()
    {
        return array(
            [Null,           Null,           self::DATE1_SQL,self::DATE1_SQL],
            [Null,           self::DATE2_SQL,self::DATE1_SQL,self::DATE2_SQL],
            [self::DATE1_SQL,Null,           self::DATE1_SQL,self::DATE1_SQL],
            [self::DATE1_TS, Null,           self::DATE1_SQL,self::DATE1_SQL],
            [self::DATE_NOW, Null,           self::DATE1_SQL,self::DATE1_SQL],
            [self::DATE1_SQL,self::DATE1_SQL,self::DATE1_SQL,self::DATE1_SQL],
            [self::DATE1_SQL,self::DATE2_SQL,self::DATE1_SQL,self::DATE2_SQL],
            [self::DATE_NOW, self::DATE2_SQL,self::DATE1_SQL,self::DATE2_SQL],
            [self::DATE1_TINY,self::DATE2_TINY,self::DATE1_SQL,self::DATE2_SQL],
            [self::DATE1_SHORT,self::DATE2_SHORT,self::DATE1_SQL,self::DATE2_SQL],
            [self::DATE1_LONG,self::DATE2_LONG,self::DATE1_SQL,self::DATE2_SQL],
            [Carbon::parse(self::DATE1_TINY), Carbon::parse(self::DATE2_TINY),
             self::DATE1_SQL, self::DATE2_SQL],
        );
    }

    // /**
    //  * These are functional tests; not sure how to test them
    //  * via unit testing....
    //  * @dataProvider getFormattedData
    //  */
    // public function testFormatting($in, $out, $format, $expected)
    // {
    //     $test = $this->test->make($in, $out);

    //     $this->assertSame($expected, $test->$format);
    // }

    // public function getFormattedData()
    // {
    //     return array(
            // [self::DATE1_TINY,Null, 'foo', self::DATE1_SHORT],  // default
    //         [self::DATE1_TINY,Null, 'start_foo', self::DATE1_SHORT],
    //         [self::DATE1_TINY,Null, 'start_tiny', self::DATE1_TINY],
    //         [self::DATE1_TINY,Null, 'start_short', self::DATE1_SHORT],
    //         [self::DATE1_TINY,Null, 'short_title', 'For '.self::DATE1_SHORT],
    //         [self::DATE1_TINY,Null, 'start_title', 'For '.self::DATE1_TITLE],

    //         [DateRange::NONE, Null, 'start', '(n/a)'],
    //         [DateRange::NONE, Null, 'end', '(n/a)'],
    //         [DateRange::NONE, Null, 'start_sql', Null],
    //         [DateRange::NONE, Null, 'end_sql', Null],

    //         [DateRange::NONE,self::DATE2_TINY, 'end_sql', self::DATE2_SQL],
    //         [Null,self::DATE2_TINY, 'end_tiny', self::DATE2_TINY],

    //         [self::DATE1_TINY,self::DATE2_TINY, 'short', 
    //          self::DATE1_SHORT.' &ndash; '.SELF::DATE2_SHORT],
    //         [self::DATE1_TINY,self::DATE2_TINY, 'range_short', 
    //          self::DATE1_SHORT.' &ndash; '.SELF::DATE2_SHORT],

    //         [self::DATE1_TINY,self::DATE2_TINY, 'short_title', 
    //          'From '.self::DATE1_SHORT.' to '.SELF::DATE2_SHORT],
    //         [self::DATE1_TINY,self::DATE2_TINY, 'range_short_title', 
    //          'From '.self::DATE1_SHORT.' to '.SELF::DATE2_SHORT],

    //         [self::DATE1_TINY,self::DATE2_TINY, 'title', 
    //          'From '.self::DATE1_TITLE.' to '.SELF::DATE2_TITLE],
    //         [self::DATE1_TINY,self::DATE2_TINY, 'range_title', 
    //          'From '.self::DATE1_TITLE.' to '.SELF::DATE2_TITLE],

    //         [self::DATE1_TINY,self::DATE2_TINY, 'url', 
    //          'start='.self::DATE1_SQL.'&end='.SELF::DATE2_SQL],
    //     );
    // }
    // 
    // public function testShouldGetDecimalTimeViaClosure()
    // {
    //     $test = $this->test->make('10:00am', '10:30am');
    //     $this->assertEquals(10, $test->decimal);
    //     $this->assertEquals(10, $test->start_decimal);
    //     $this->assertEquals(10.5, $test->end_decimal);
    // }



    /**
     * @dataProvider getRecordsToCheckForSameDay
     */
    public function testSameDay($start, $end, $expected)
    {
        $test = $this->test->make($start, $end);
        $this->assertEquals($expected, $test->isSameDay());
        $this->assertEquals($expected, $test->onSameDay());
    }

    public function getRecordsToCheckForSameDay()
    {
        return array(
            [self::DATE1_SQL, self::DATE1_SQL, True],  // same dates
            [self::DATE1_SQL, self::DATE2_SQL, False], // diff dates
            [self::DATE1_SQL, self::DATE1_LONG, True], // same date, w/datetime
            [self::DATE1_LONG, self::DATE2_SQL, False], // diff date, w/datetime
            [DateRange::NONE, DateRange::NONE, True], // no date for either
            [self::DATE1_SQL, DateRange::NONE, True], // no date? always match
        );
    }

    public function testCanRepresentFullDay()
    {
        $this->config->setup(['styles.full'=>'Y-m-d H:i:s']);
        $test = $this->test->make(self::DATE1_LONG,Null);

        $test->fullDay();
        $this->assertEquals(self::DATE1_SQL.' 00:00:00', $test->start_full);
        $this->assertEquals(self::DATE1_SQL.' 23:59:59', $test->end_full);
    }

    /**
     * @dataProvider getOverlappingDates
     */
    public function testOverlappingDates($start1, $end1, $expected)
    {
        $test1 = $this->test->make($start1, $end1);
        $test2 = $this->test->make('1/4/2014', '1/8/2014');

        $this->assertSame($expected, $test1->overlaps($test2));
        $this->assertSame($expected, $test2->overlaps($test1));
    }

    public function getOverlappingDates()
    {
        return array(
            ['1/1/2014', '1/2/2014', False],
            ['1/1/2014', '1/5/2014', True],
            ['1/5/2014', '1/6/2014', True],
            ['1/7/2014', '1/9/2014', True],
            ['1/8/2014', '1/9/2014', False],
            ['1/9/2014', '1/10/2014', False],
        );
    }

    /**
     * @dataProvider getAdjacentDates
     */
    public function testAdjacency($start1, $end1, $expected)
    {
        $test1 = $this->test->make($start1, $end1);
        $test2 = $this->test->make('1/4/2014', '1/8/2014');

        $this->assertSame($expected, $test1->isAdjacentTo($test2));
    }

    public function getAdjacentDates()
    {
        return array(
            ['1/1/2014','1/2/2014', False],
            ['1/1/2014','1/4/2014', True],
            ['1/8/2014','1/9/2014', True],
            ['1/9/2014','1/10/2014', False],
        );
    }

    public function testShouldGetCalculatedData()
    {
        $this->config->setup(['calculations'=>'Kalani\\DateRange\\Calculator']);
        $test = $this->test->make('10:00:00', '12:00:00');

        $this->assertEquals(2, $test->hours);
        $this->assertEquals(2, $test->hours());
    }

    public function testShouldReturnZeroLengthIfNoStart()
    {
        $test = $this->test->make(DateRange::NONE, DateRange::NONE);

        $this->assertEquals(0, $test->hours);
    }

    public function testShouldReturnZeroLengthIfNoEnd()
    {
        $test = $this->test->make(null, DateRange::NONE);

        $this->assertEquals(0, $test->hours());
    }

}



