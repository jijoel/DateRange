<?php

class MockConfig implements Illuminate\Contracts\Config\Repository
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

    public function has($key) {}
    public function set($key, $value=null) {}
    public function prepend($key, $value) {}
    public function push($key, $value) {}

}

