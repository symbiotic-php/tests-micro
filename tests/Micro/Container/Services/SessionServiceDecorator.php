<?php

namespace Symbiotic\Tests\Micro\Container\Services;


class SessionServiceDecorator
{
    /**
     * @var SessionService|null
     */
    protected $session = null;

    public function __construct(SessionService $service)
    {
       $this->session = $service;
    }

    public function get($name)
    {
        return $this->session->get($name);
    }
}
