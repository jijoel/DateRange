<?php namespace Kalani\DateRange;

/**
 * An example of how to use your own calculator object
 * for calculating all numbers
 */
class ExampleCalculator extends Calculator
{
    /**
     * Round down to the nearest 15 minutes
     * As a special case, round the first period
     * down to 0.
     */
    public function hours($downTo=15, $decimalPlaces=2)
    {
        if (! $this->has(['start','end']))
            return 0;

        $diff = $this->start->diffInMinutes($this->end);
        $minutes = $diff - ($downTo / 2);

        $hours = $minutes / 60;
        $periodsPerHour = 60 / $downTo;
        $roundedHours = round(
            round($hours * $periodsPerHour) / $periodsPerHour, 
            $decimalPlaces
        );

        $whole = floor($roundedHours);
        $fraction = round($roundedHours - $whole,2);
        $periodLength = round(1.0 / $periodsPerHour,2);

        if ($fraction == $periodLength && $diff % $downTo === 0)
            return ($roundedHours - $periodLength);

        return $roundedHours;
    }

}

