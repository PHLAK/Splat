<?php

namespace Tests;

use Yoast\PHPUnitPolyfills\TestCases\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $backupStaticAttributes = true;
}
