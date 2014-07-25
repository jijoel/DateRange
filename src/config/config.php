<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Defaults when there is no date
    |--------------------------------------------------------------------------
    |
    | This is an array of values to return when the developer has explicitly
    | set no date in a field. It signifies that there is no date, or that a
    | date range is open-ended (eg, 1/1/2014 - ???).
    |
    | The developer gets this by using DateRange::NONE as a field value.
    |
    */

    'none' => array(

        /**
         * Default when no value is entered
         */
        'default' => '(n/a)',

        /**
         * When using the 'sql' format, set the n/a default to this:
         */
        'sql' => Null,
        
    ),


    /*
    |--------------------------------------------------------------------------
    | Styles
    |--------------------------------------------------------------------------
    |
    | Return specific php date format strings to use when a given style 
    | is requested.
    |
    | To request a specific style, a developer will use <value>_<style>
    | (eg, start_short for a short format of the start date)
    |
    */
    'styles' => array(

        /**
         * Default format, when no format is sent
         */
        'default' => 'n/j/Y',

        'short' => 'n/j/Y',
        'tiny' => 'n/j/y',
        'pad' => 'm/d/y',
        'padded' => 'm/d/Y',

        'sql' => 'Y-m-d',
        'full' => 'Y-m-d H:i:s',

        'title' => 'l, F j, Y',
        'long' => 'D, M j, Y h:ia',

        'month' => 'F Y',
        'time' => 'g:ia',
        'url' => 'Y-m-d',
    ),

    /*
    |--------------------------------------------------------------------------
    | Range Delimiters
    |--------------------------------------------------------------------------
    |
    | Each format can have a range delimiter. This will enter text before,
    | in the middle, and after two dates. In the case the start and end dates
    | are the same, the 'only' value will put data before the resulting date.
    |
    | This can be combined with the format.
    |
    */
    'range' => array(

        /**
         * Use this for any formats without an explicit range delimiter
         */
        'default' => [
            'before' => '',
            'middle' => ' &ndash; ',
            'end' => '',
            'only' => '',
        ],

        /**
         * Format title text with this
         */
        'title' => [
            'before' => 'From ',
            'middle' => ' to ',
            'end'    => '',
            'only'   => 'For ',
        ],

        'url' => [
            'before' => 'start=',
            'middle' => '&end=',
            'end'    => '',
            'only'   => 'date=',
        ],
    ),

    /*
    |--------------------------------------------------------------------------
    | Calculations
    |--------------------------------------------------------------------------
    |
    | We can use a closure to calculate other values. 
    |
    | Pass $start and $end or $date values to the closure. 
    |
    */
    'calculations' => array(

        'days' => function($start, $end) { return $end->diffInDays($start); },

        'decimal' => function($date) {
            $hours = $date->hour + ($date->minute / 60);
            return round($hours,1);
        },

    ),

);

