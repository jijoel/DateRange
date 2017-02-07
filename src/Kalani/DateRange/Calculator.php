<?php namespace Kalani\DateRange;

/**
 * Calculate values from a date range
 */
class Calculator
{
    protected $start;
    protected $end;

    function __construct($start, $end=null)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function hours($roundToMinutes=1, $roundToDecimalPlaces=2)
    {
        if (! $this->has(['start','end']))
            return 0;

        $minutes = $this->start->diffInMinutes($this->end);
        $hours = $minutes / 60;

        $periodsPerHour = 60 / $roundToMinutes;
        $roundedHours = round($hours * $periodsPerHour) / $periodsPerHour;

        return round($roundedHours, $roundToDecimalPlaces);
    }

    public function hoursRoundedToNearest($minutes=1, $decimalPlaces=2)
    {
        return $this->hours($minutes, $decimalPlaces);
    }

    public function days() 
    { 
        if (! $this->has(['start','end']))
            return 0;

        return $this->end->diffInDays($this->start); 
    }

    public function months() 
    {
        if (! $this->has(['start','end']))
            return 0;

        return $this->end->copy()
            ->addDays(14)->endOfDay()
            ->diffInMonths(
                $this->start->copy()->startOfDay()
            );
    }

    public function decimal()
    {
        if (! $this->has(['start']))
            return 0;

        $hours = $this->start->hour
               + ($this->start->minute / 60);

        return round($hours, 1);
    }

    protected function has($fields=['start','end'])
    {
        foreach ($fields as $field)
            if (! $this->$field instanceof \Carbon\Carbon)
                return False;

        return True;
    }
}
