DateRange
==========

The DateRange class provides a convenient way to handle formatted date ranges. It lets you do things like this:

```php
$range = DateRange::make('2/1/2014');
$range->start_sql;     // returns 2014-02-01
$range->title          // returns "For Saturday, February 1, 2014" 
```

Using the DateRange class, you can initialize dates from many different formats, and output them to styles specified via the configuration file.

Several date styles are included by default:

    Style Name  PHP Format      Example
    ----------- --------------- --------------------------
    default     n/j/Y           7/25/2014
    short       n/j/Y           7/25/2014
    tiny        n/j/y           7/25/14
    pad         m/d/y           07/25/14
    padded      m/d/Y           07/25/2014
    sql         Y-m-d           2014-07-25
    full        Y-m-d H:i:s     2014-07-25 08:53:27
    title       l, F j, Y       For Friday, July 25, 2014
    long        D, M j, Y h:ia  Fri, Jul 25, 2014 08:53am
    month       F Y             July 2014
    time        g:ia            8:53am
    url         Y-m-d           date=2014-07-25

Several range styles are also included by default:

    Style Name  Example
    ----------- -----------------------------------------------------
    title       From Friday, July 25, 2014 to Saturday, July 26, 2014
    url         start=2014-07-25&end=2014-07-26
    short       7/25/2014 – 7/26/2014
    short_title From 7/25/2014 to 7/26/2014



Installation
------------

Install the package via Composer. Edit your composer.json file to require kalani/date-range.

    "require": {
        "kalani/date-range": "dev-master"
    }

Next, update Composer from the terminal:

    composer update

Then, add the service provider to the providers array in app\config\app.php:

    'Kalani\DateRange\DateRangeServiceProvider',

You can also add an alias for the Facade:

    'DateRange' => 'Kalani\DateRange\Facades\DateRange',

If you would like to define your own date styles, publish the configuration file:

    php artisan config:publish kalani/date-range


Usage
--------

### Setting start and end dates

Enter a start and (optional) end date. DateRange uses Carbon internally to set dates, so you can use any method that Carbon uses to make a DateRange object. For instance, all of these strings are valid:

    DateRange::make();                                // defaults to today
    DateRange::make('today');
    DateRange::make('today', 'tomorrow');
    DateRange::make('today', 'next Friday');
    DateRange::make('today', 'last day of this month');
    DateRange::make('today', 'first day of next month');
    ...etc.

You can also build a DateRange object from a DateTime object:

    DateRange::make(new DateTime("2014-01-09 11:14:15.638276"));

or from a timestamp:

    DateRange::make(1388679600);

You can also specify that there is no date, by entering DateRange::none():

    DateRange::make(DateRange::none());
    DateRange::make('today', DateRange::none());

This can be useful to signify that something has a beginning, but no fixed end, or vice-versa.

If you do not specify an end date, the end will automatically be set to the start.


### Outputting Dates and Date Ranges

You can output the start date, end date, or full range in a variety of styles. Specific styles are defined in the configuration file. Outputting data is done using the `value_style` notation, where `value` is start, end, or range, and `style` is the output style you want to use. So,

    start_short       output the start date in the short format (n/j/Y)
    end_short         output the end date in the short format
    range_short       output the range in a short format
    short             output the range in a short format

The predefined formats are described above.

Each format can also have a range delimiter. This will enter text before, in the middle, and after two dates. If the start and end date are the same, it will use an alternate notation. So, for instance, a title will show up in one of these two ways:

    For Friday, July 25, 2014                                       // for one date only
    From Friday, July 25, 2014 to Saturday, July 26, 2014           // for a range of dates

You can also combine the style with the range delimiters, eg:

    range_short_title:   From 7/25/2014 to 7/26/2014
    short_title:         (same)

You can also specify a closure in the configuration file to do more advanced calculations, such as the difference in days between two dates. For instance,

    'days' => function($start, $end) { 
        return $end->diffInDays($start); 
    },

Note that, if the dates are not valid objects (eg, one or both of the dates is not available), the configuration value `none.calculations` will be returned for any calculation.

If you want to change the default returned for a non-existing value, you can call the format method:

    $a = DateRange::make(DateRange::none());
    $a->format('start','style','format-if-empty');


### Other Range Functions

There are several comparison functions, including:

    isSameDay()           // start and end are on the same day
    onSameDay()           // (same)
    fullDay()             // from the beginning of day1 to the end of day2
    overlaps($other)      // returns True if this range overlaps another
    isAdjacentTo($other)  // returns True if this range is adjacent to another

