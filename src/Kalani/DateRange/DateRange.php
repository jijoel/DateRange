<?php namespace Kalani\DateRange;

use App;
use DateTime;
use Carbon\Carbon;
use Illuminate\Config\Repository as Config;


class DateRange
{
    const NONE = -1;

    protected $config;
    protected $start;
    protected $end;

    public function __construct(Config $config)
    {
        $this->config = $config;
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
            throw new DateOrderException('Start date must precede end date');
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

    public function format($date, $style, $default=Null)
    {
        if ($date == 'range')
            return $this->applyStyleToRange($style, $default);

        return $this->applyStyleToDate($date, $style, $default);
    }

    public function __get($name)
    {
        if (method_exists($this, $name)) 
            return $this->$name();

        list($value, $process) = $this->splitValueFromProcess($name);

        $closure = $this->getConfig("calculations.$process");
        if ($closure)
            return($this->executeClosure($value,$closure));

        if ($value == 'range')
            return $this->applyStyleToRange($process, Null);

        return $this->applyStyleToDate($value, $process, Null);
    }

    private function splitValueFromProcess($value)
    {
        foreach(['start','end','range'] as $date)
            if (strpos($value, $date) === 0)
                return array($date, substr($value, strlen($date)+1));

        return array('range', $value);
    }

    private function executeClosure($value, $closure)
    {
        if ($value == 'range') {
            if ($this->canCompareDates())
                return $closure($this->start, $this->end);

            return $this->getConfig('none.calculations');
        }

        if ($this->isCarbonObject($this->$value))
            return $closure($this->$value);

        return $this->getConfig('none.calculations');
    }

    private function applyStyleToRange($requestedStyle, $default)
    {
        list($delimiters, $style) = $this->splitDelimitersFromStyle($requestedStyle);

        if ($this->start == $this->end)
            return $delimiters['only'] . $this->formatDate($this->start, $style, $default);

        return $delimiters['before']
            . $this->formatDate($this->start, $style, $default)
            . $delimiters['middle'] 
            . $this->formatDate($this->end, $style, $default)
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

    private function applyStyleToDate($value, $style, $default)
    {
        $prefix = $this->getConfig("range.$style.only");
        if ($prefix)
            return $prefix.$this->formatDate($this->$value(), $style, $default);
        
        return $this->formatDate($this->$value(), $style, $default);
    }

    public function formatDate($date, $style, $default=Null)
    {
        if ( ! is_object($date)) {
            $default = $this->getConfig('none.'.$style, 'n/a');
            return ($default<>'n/a') ? $default : $date;            
        }

        $formatString = $this->getConfig('styles.'.$style);
        if ($formatString)
            return $date->format($formatString);

        if ($default)
            return $date->format($default);

        return $date->format($this->getConfig('styles.default'));
    }


// // Helper Methods -------------------------------------------------------------

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
