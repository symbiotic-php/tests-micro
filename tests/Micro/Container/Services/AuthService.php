<?php

namespace Symbiotic\Tests\Micro\Container\Services;


class AuthService
{
    /**
     * @var null
     */
    protected $session = null;

    public function __construct(SessionService $session = null)
    {
         $this->session = $session;
    }

   public function setSession(SessionService $service)
   {
       $this->session = $service;
   }

   public function getName()
   {
       return $this->session->get('name');
   }
}
