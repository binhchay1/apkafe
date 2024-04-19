<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace LassoVendor\Symfony\Polyfill\Util;

use LassoVendor\PHPUnit\Framework\AssertionFailedError;
use LassoVendor\PHPUnit\Framework\Test;
use LassoVendor\PHPUnit\Framework\TestListener as TestListenerInterface;
use LassoVendor\PHPUnit\Framework\TestSuite;
use LassoVendor\PHPUnit\Framework\Warning;
use LassoVendor\PHPUnit\Framework\WarningTestCase;
class TestListenerForV9 extends TestSuite implements TestListenerInterface
{
    private $suite;
    private $trait;
    public function __construct(TestSuite $suite = null)
    {
        if ($suite) {
            $this->suite = $suite;
            $this->setName($suite->getName() . ' with polyfills enabled');
            $this->addTest($suite);
        }
        $this->trait = new TestListenerTrait();
    }
    public function startTestSuite(TestSuite $suite) : void
    {
        $this->trait->startTestSuite($suite->tests()[0]);
    }
    public function addError(Test $test, \Throwable $t, float $time) : void
    {
        $this->trait->addError($test, $t, $time);
    }
    public function addWarning($test, Warning $e = null, float $time = null) : void
    {
        if (\is_string($test)) {
            parent::addWarning($test);
        }
    }
    public function addFailure(Test $test, AssertionFailedError $e, float $time) : void
    {
        $this->trait->addError($test, $e, $time);
    }
    public function addIncompleteTest(Test $test, \Throwable $t, float $time) : void
    {
    }
    public function addRiskyTest(Test $test, \Throwable $t, float $time) : void
    {
    }
    public function addSkippedTest(Test $test, \Throwable $t, float $time) : void
    {
    }
    public function endTestSuite(TestSuite $suite) : void
    {
    }
    public function startTest(Test $test) : void
    {
    }
    public function endTest(Test $test, float $time) : void
    {
    }
    public static function warning($message) : WarningTestCase
    {
        return new WarningTestCase($message);
    }
    protected function setUp() : void
    {
        TestListenerTrait::$enabledPolyfills = $this->suite->getName();
    }
    protected function tearDown() : void
    {
        TestListenerTrait::$enabledPolyfills = \false;
    }
}
