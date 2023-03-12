<?php

namespace Symbiotic\Tests\Micro\Container\Services;


class SessionService
{
    protected $session = [];

    public function __construct(array $data = null)
    {
        if (is_array($data)) {
            $this->session = $data;
        }
    }

    public function get($name)
    {
        return isset($this->session[$name]) ? $this->session[$name] : null;
    }
}
