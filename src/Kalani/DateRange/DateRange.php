<?php namespace Kalani\DateRange;

use DateTime;
use Carbon\Carbon;
use Illuminate\Config\Repository as Config;


class DateRange
{
    const NONE = -1;

    protected $calculator;
    protected $config;
    protected $start;
    protected $end;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->calculator = $this->getConfig(
            'calculations', 'Kalani\\DateRange\\Calculator'
        );
    }

    public function make($start=Null, $end=Null)
    {
        $instance = clone $this;

        $instance->setDates($start, $end);

        return $instance;
    }

    public function none()
    {
        return self::NONE;
    }

    public function setDates($start, $end)
    {
        if (! $end)
            $end = $start;

        $this->start = $this->getCarbonDate($start);
        $this->end = $this->getCarbonDate($end);

        if ($this->canCompareDates() && $this->start->gt($this->end))
            $this->swapDates();
    }

    private function swapDates()
    {
        $temp = $this->start;
        $this->start = $this->end;
        $this->end = $temp;
    }

    private function getCarbonDate($date)
    {
        if ($date === self::NONE)
            return $this->getConfig('none.default');

        if ($date instanceof Carbon)
            return $date;

        if ($date instanceof DateTime)
            return Carbon::instance($date);

        if (is_numeric($date))
            return Carbon::createFromTimestamp($date);

        return Carbon::parse($date);
    }

    public function start()
    {
        return $this->start;
    }

    public function end()
    {
        return $this->end;
    }

    public function isSameDay()
    {
        if ( ! $this->canCompareDates())
            return True;

        if ($this->start == $this->end)
            return True;

        return $this->start->copy()->startOfDay()
            ->eq($this->end->copy()->startOfDay());
    }

    public function onSameDay()
    {
        return $this->isSameDay();
    }

    public function fullDay()
    {
        $this->start->startOfDay();
        $this->end->endOfDay();

        return $this;
    }

    public function overlaps(DateRange $other)
    {
        return ($this->start->lt($other->end)
            && $this->end->gt($other->start));
    }

    public function isAdjacentTo(DateRange $other)
    {
        return ($this->start->eq($other->end)
            || $this->end->eq($other->start));
    }


// Get formatted output -------------------------------------------------------

    public function __toString()
    {
        return $this->range_default;
    }

    public function __call($name, $params)
    {
        $calculator = new $this->calculator($this->start, $this->end);
        if (method_exists($calculator, $name))
            return call_user_func_array([$calculator, $name], $params);
    }

    public function __get($name)
    {
        if (method_exists($this, $name)) 
            return $this->$name();

        list($value, $style) = $this->getValueAndStyleOfRequestedAttribute($name);

        // instantiating here because the start and end
        // values are not fixed.
        $calculator = new $this->calculator($this->start, $this->end);
        if (method_exists($calculator, $name))
            return $calculator->$name();

        if ($value == 'range')
            return $this->applyStyleToRange($style, Null, Null);

        return $this->applyStyleToDate($value, $style, Null, Null);
    }

    private function getValueAndStyleOfRequestedAttribute($value)
    {
        foreach(['start','end','range'] as $date)
            if (strpos($value, $date) === 0)
                return array($date, substr($value, strlen($date)+1));

        return array('range', $value);
    }

    public function format($date, $style, $defaultFormat=Null, $defaultValue=Null)
    {
        if ($date == 'range')
            return $this->applyStyleToRange($style, $defaultFormat, $defaultValue);

        return $this->applyStyleToDate($date, $style, $defaultFormat, $defaultValue);
    }

    private function applyStyleToRange($requestedStyle, $defaultFormat, $defaultValue)
    {
        list($delimiters, $style) = $this->splitDelimitersFromStyle($requestedStyle);

        if ($this->start == $this->end)
            return $delimiters['only'] . $this->formatDate($this->start, $style, $defaultFormat, $defaultValue);

        return $delimiters['before']
            . $this->formatDate($this->start, $style, $defaultFormat, $defaultValue)
            . $delimiters['middle'] 
            . $this->formatDate($this->end, $style, $defaultFormat, $defaultValue)
            . $delimiters['after'];
    }

    private function splitDelimitersFromStyle($requestedStyle)
    {
        if ( ! strpos($requestedStyle, '_')) 
            return array(
                $this->getDelimiters($requestedStyle), 
                $requestedStyle
            );

        list($dateStyle, $rangeStyle) = explode('_', $requestedStyle);        

        return array(
            $this->getDelimiters($rangeStyle), 
            $dateStyle
        );
    }

    private function getDelimiters($style)
    {
        $delimiters = $this->getConfig('range.'.$style);
        if ($delimiters)
            return $delimiters;

        return $this->getConfig('range.default');
    }

    private function applyStyleToDate($value, $style, $defaultFormat, $defaultValue)
    {
        $prefix = $this->getConfig("range.$style.only");
        if ($prefix)
            return $prefix.$this->formatDate($this->$value(), $style, $defaultFormat, $defaultValue);
        
        return $this->formatDate($this->$value(), $style, $defaultFormat, $defaultValue);
    }

    public function formatDate($date, $style, $defaultFormat=Null, $defaultValue=Null)
    {
        if ( ! is_object($date)) {
            if ( ! is_null($defaultValue))
                return $defaultValue;

            $default = $this->getConfig('none.'.$style, 'n/a');
            return ($default<>'n/a') ? $default : $date;
        }

        $formatString = $this->getConfig('styles.'.$style);
        if ($formatString)
            return $date->format($formatString);

        if ($defaultFormat)
            return $date->format($defaultFormat);

        return $date->format($this->getConfig('styles.default'));
    }

// Helper Methods -------------------------------------------------------------

    private function getConfig($value, $default='')
    {
        return $this->config->get('date-range::'.$value, $default);
    }

    private function canCompareDates()
    {
        return $this->isCarbonObject($this->start) 
            && $this->isCarbonObject($this->end);
    }

    private function isCarbonObject($object)
    {
        return $object instanceof Carbon;
    }

}
