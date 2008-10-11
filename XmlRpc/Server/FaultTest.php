<?php
// Call Zend_XmlRpc_Server_FaultTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    require_once 'Zend/TestHelper.php';
    define("PHPUnit_MAIN_METHOD", "Zend_XmlRpc_Server_FaultTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'Zend/XmlRpc/Server.php';
require_once 'Zend/XmlRpc/Server/Fault.php';

/**
 * Test case for Zend_XmlRpc_Server_Fault
 *
 * @package Zend_XmlRpc
 * @subpackage UnitTests
 * @version $Id$
 */
class Zend_XmlRpc_Server_FaultTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Zend_XmlRpc_Server_FaultTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Zend_XmlRpc_Server_Fault::getInstance() test
     */
    public function testGetInstance()
    {
        $e = new Zend_XmlRpc_Server_Exception('Testing fault', 411);
        $fault = Zend_XmlRpc_Server_Fault::getInstance($e);

        $this->assertTrue($fault instanceof Zend_XmlRpc_Server_Fault);
    }

    /**
     * Zend_XmlRpc_Server_Fault::attachFaultException() test
     */
    public function testAttachFaultException()
    {
        Zend_XmlRpc_Server_Fault::attachFaultException('zxrs_fault_test_exception');
        $e = new zxrs_fault_test_exception('test exception', 411);
        $fault = Zend_XmlRpc_Server_Fault::getInstance($e);
        $this->assertEquals('test exception', $fault->getMessage());
        $this->assertEquals(411, $fault->getCode());
        Zend_XmlRpc_Server_Fault::detachFaultException('zxrs_fault_test_exception');

        $exceptions = array(
            'zxrs_fault_test_exception', 
            'zxrs_fault_test_exception2', 
            'zxrs_fault_test_exception3'
        );
        Zend_XmlRpc_Server_Fault::attachFaultException($exceptions);
        foreach ($exceptions as $class) {
            $e = new $class('test exception', 411);
            $fault = Zend_XmlRpc_Server_Fault::getInstance($e);
            $this->assertEquals('test exception', $fault->getMessage());
            $this->assertEquals(411, $fault->getCode());
        }
        Zend_XmlRpc_Server_Fault::detachFaultException($exceptions);
    }

    /**
     * Tests ZF-1825
     * @return void
     */
    public function testAttachFaultExceptionAllowsForDerivativeExceptionClasses()
    {
        Zend_XmlRpc_Server_Fault::attachFaultException('zxrs_fault_test_exception');
        $e = new zxrs_fault_test_exception4('test exception', 411);
        $fault = Zend_XmlRpc_Server_Fault::getInstance($e);
        $this->assertEquals('test exception', $fault->getMessage());
        $this->assertEquals(411, $fault->getCode());
        Zend_XmlRpc_Server_Fault::detachFaultException('zxrs_fault_test_exception');
    }

    /**
     * Zend_XmlRpc_Server_Fault::detachFaultException() test
     */
    public function testDetachFaultException()
    {
        Zend_XmlRpc_Server_Fault::attachFaultException('zxrs_fault_test_exception');
        $e = new zxrs_fault_test_exception('test exception', 411);
        $fault = Zend_XmlRpc_Server_Fault::getInstance($e);
        $this->assertEquals('test exception', $fault->getMessage());
        $this->assertEquals(411, $fault->getCode());
        Zend_XmlRpc_Server_Fault::detachFaultException('zxrs_fault_test_exception');
        $fault = Zend_XmlRpc_Server_Fault::getInstance($e);
        $this->assertEquals('Unknown error', $fault->getMessage());
        $this->assertEquals(404, $fault->getCode());


        $exceptions = array(
            'zxrs_fault_test_exception', 
            'zxrs_fault_test_exception2', 
            'zxrs_fault_test_exception3'
        );
        Zend_XmlRpc_Server_Fault::attachFaultException($exceptions);
        foreach ($exceptions as $class) {
            $e = new $class('test exception', 411);
            $fault = Zend_XmlRpc_Server_Fault::getInstance($e);
            $this->assertEquals('test exception', $fault->getMessage());
            $this->assertEquals(411, $fault->getCode());
        }
        Zend_XmlRpc_Server_Fault::detachFaultException($exceptions);
        foreach ($exceptions as $class) {
            $e = new $class('test exception', 411);
            $fault = Zend_XmlRpc_Server_Fault::getInstance($e);
            $this->assertEquals('Unknown error', $fault->getMessage());
            $this->assertEquals(404, $fault->getCode());
        }
    }

    /**
     * Zend_XmlRpc_Server_Fault::attachObserver() test
     */
    public function testAttachObserver()
    {
        Zend_XmlRpc_Server_Fault::attachObserver('zxrs_fault_observer');
        $e = new Zend_XmlRpc_Server_Exception('Checking observers', 411);
        $fault = Zend_XmlRpc_Server_Fault::getInstance($e);
        $observed = zxrs_fault_observer::getObserved();
        zxrs_fault_observer::clearObserved();
        Zend_XmlRpc_Server_Fault::detachObserver('zxrs_fault_observer');

        $this->assertTrue(!empty($observed));
        $f = array_shift($observed);
        $this->assertTrue($f instanceof Zend_XmlRpc_Server_Fault);
        $this->assertEquals('Checking observers', $f->getMessage());
        $this->assertEquals(411, $f->getCode());
    }

    /**
     * Zend_XmlRpc_Server_Fault::detachObserver() test
     */
    public function testDetachObserver()
    {
        Zend_XmlRpc_Server_Fault::attachObserver('zxrs_fault_observer');
        $e = new Zend_XmlRpc_Server_Exception('Checking observers', 411);
        $fault = Zend_XmlRpc_Server_Fault::getInstance($e);
        zxrs_fault_observer::clearObserved();
        Zend_XmlRpc_Server_Fault::detachObserver('zxrs_fault_observer');

        $e = new Zend_XmlRpc_Server_Exception('Checking observers', 411);
        $fault = Zend_XmlRpc_Server_Fault::getInstance($e);
        $observed = zxrs_fault_observer::getObserved();
        $this->assertTrue(empty($observed));
    }

    /**
     * getCode() test
     */
    public function testGetCode()
    {
        $e = new Zend_XmlRpc_Server_Exception('Testing fault', 411);
        $fault = Zend_XmlRpc_Server_Fault::getInstance($e);

        $this->assertEquals(411, $fault->getCode());
    }

    /**
     * getMessage() test
     */
    public function testGetMessage()
    {
        $e = new Zend_XmlRpc_Server_Exception('Testing fault', 411);
        $fault = Zend_XmlRpc_Server_Fault::getInstance($e);

        $this->assertEquals('Testing fault', $fault->getMessage());
    }

    /**
     * __toString() test
     */
    public function test__toString()
    {
        $dom  = new DOMDocument('1.0', 'ISO-8859-1');
        $r    = $dom->appendChild($dom->createElement('methodResponse'));
        $f    = $r->appendChild($dom->createElement('fault'));
        $v    = $f->appendChild($dom->createElement('value'));
        $s    = $v->appendChild($dom->createElement('struct'));

        $m1   = $s->appendChild($dom->createElement('member'));
        $m1->appendChild($dom->createElement('name', 'faultCode'));
        $cv   = $m1->appendChild($dom->createElement('value'));
        $cv->appendChild($dom->createElement('int', 411));

        $m2   = $s->appendChild($dom->createElement('member'));
        $m2->appendChild($dom->createElement('name', 'faultString'));
        $sv   = $m2->appendChild($dom->createElement('value'));
        $sv->appendChild($dom->createElement('string', 'Testing fault'));

        $xml = $dom->saveXML();

        $e = new Zend_XmlRpc_Server_Exception('Testing fault', 411);
        $fault = Zend_XmlRpc_Server_Fault::getInstance($e);

        $this->assertEquals($xml, $fault->__toString());
    }


}

class zxrs_fault_test_exception extends Exception {}
class zxrs_fault_test_exception2 extends Exception {}
class zxrs_fault_test_exception3 extends Exception {}
class zxrs_fault_test_exception4 extends zxrs_fault_test_exception {}

class zxrs_fault_observer 
{
    private static $_instance = false;

    public $observed = array();

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function observe(Zend_XmlRpc_Server_Fault $fault)
    {
        self::getInstance()->observed[] = $fault;
    }

    public static function clearObserved()
    {
        self::getInstance()->observed = array();
    }

    public static function getObserved()
    {
        return self::getInstance()->observed;
    }
}

// Call Zend_XmlRpc_Server_FaultTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Zend_XmlRpc_Server_FaultTest::main") {
    Zend_XmlRpc_Server_FaultTest::main();
}
