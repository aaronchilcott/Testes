<?php

namespace Provider\Fixture;
use Testes\Fixture\FixtureAbstract;

class Address extends FixtureAbstract
{
    public function setUp()
    {
        $this['id']       = md5(rand() . microtime() . rand());
        $this['street']   = '123 Testes Circle';
        $this['city']     = 'Santa Cruz';
        $this['state']    = 'California';
        $this['postcode'] = '95076';
        $this['country']  = 'USA';
    }

    public function tearDown()
    {

    }
}