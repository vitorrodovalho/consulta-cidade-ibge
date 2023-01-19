<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public $headerJson = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];
}
