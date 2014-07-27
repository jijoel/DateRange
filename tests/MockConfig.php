<?php

class MockConfig extends Illuminate\Config\Repository
{
    private $keys;

    public function __construct(){}

    public function get($key, $default='')
    {
        $key = str_replace('date-range::', '', $key);

        if (isset($this->keys[$key]))
            return $this->keys[$key];

        // var_dump($key);
        return $default;
    }

    public function setup(array $values)
    {
        $this->keys = $values;
    }

}

