<?php
// Call Zend_Amf_ServerTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Zend_Amf_ServerTest::main");
}

/**
 * Test helper
 */
require_once 'Zend/TestHelper.php';
require_once 'Zend/Amf/Server.php';
require_once 'Zend/Amf/Request.php';
require_once 'Zend/Amf/Parse/TypeLoader.php';
require_once 'Zend/Amf/Value/Messaging/RemotingMessage.php';

class Zend_Amf_ServerTest extends PHPUnit_Framework_TestCase
{
    protected $_server;

    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite("Zend_Amf_ServerTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp()
    {
        $this->_server = new Zend_Amf_Server();
        $this->_server->setProduction(false);
    }

    public function testDown()
    {
        unset($this->_server);
    }

    /**
     * Call as method call
     *
     * Returns: void
     */
    public function test__construct()
    {
        $this->assertTrue($this->_server instanceof Zend_Amf_Server);
    }

    public function testIsProductionByDefault()
    {
        $this->_server = new Zend_Amf_Server;
        $this->assertTrue($this->_server->isProduction());
    }

    public function testProductionFlagShouldBeMutable()
    {
        $this->testIsProductionByDefault();
        $this->_server->setProduction(false);
        $this->assertFalse($this->_server->isProduction());
        $this->_server->setProduction(true);
        $this->assertTrue($this->_server->isProduction());
    }

    public function testSetClass()
    {
        $this->_server->setClass('Zend_Amf_testclass', 'test');
        $methods = $this->_server->listMethods();
        $this->assertTrue(in_array('test.test1', $methods));
        $this->assertTrue(in_array('test.test2', $methods));
        $this->assertFalse(in_array('test._test3', $methods));
        $this->assertFalse(in_array('test.__construct', $methods));
    }

    /**
     * @expectedException Zend_Amf_Server_Exception
     */
    public function testSetClassShouldRaiseExceptionOnInvalidClassname()
    {
        $this->_server->setClass('foobar');
    }

    /**
     * @expectedException Zend_Amf_Server_Exception
     */
    public function testSetClassShouldRaiseExceptionOnInvalidClasstype()
    {
        $this->_server->setClass(array('foobar'));
    }

    /**
     * @expectedException Zend_Amf_Server_Exception
     */
    public function testSetClassShouldRaiseExceptionOnDuplicateMethodName()
    {
        $this->_server->setClass('Zend_Amf_testclass', 'tc');
        $this->_server->setClass('Zend_Amf_testclassPrivate', 'tc');
    }

    /**
     * addFunction() test
     *
     * Call as method call
     *
     * Expects:
     * - function:
     * - namespace: Optional; has default;
     *
     * Returns: void
     */
    public function testAddFunction()
    {
        try {
            $this->_server->addFunction('Zend_Amf_Server_testFunction', 'test');
        } catch (Exception $e) {
            $this->fail('Attachment should have worked');
        }

        $methods = $this->_server->listMethods();
        $this->assertTrue(in_array('test.Zend_Amf_Server_testFunction', $methods), var_export($methods, 1));

        try {
            $this->_server->addFunction('nosuchfunction');
            $this->fail('nosuchfunction() should not exist and should throw an exception');
        } catch (Exception $e) {
            // do nothing
        }

        $server = new Zend_Amf_Server();
        try {
            $server->addFunction(
                array(
                    'Zend_Amf_Server_testFunction',
                    'Zend_Amf_Server_testFunction2',
                ),
                'zsr'
            );
        } catch (Exception $e) {
            $this->fail('Error attaching array of functions: ' . $e->getMessage());
        }
        $methods = $server->listMethods();
        $this->assertTrue(in_array('zsr.Zend_Amf_Server_testFunction', $methods));
        $this->assertTrue(in_array('zsr.Zend_Amf_Server_testFunction2', $methods));
    }

    /**
     * @expectedException Zend_Amf_Server_Exception
     */
    public function testAddFunctionShouldRaiseExceptionForInvalidFunctionName()
    {
        $this->_server->addFunction(true);
    }

    /**
     * @expectedException Zend_Amf_Server_Exception
     */
    public function testAddFunctionShouldRaiseExceptionOnDuplicateMethodName()
    {
        $this->_server->addFunction('Zend_Amf_Server_testFunction', 'tc');
        $this->_server->addFunction('Zend_Amf_Server_testFunction', 'tc');
    }

    /**
     * Test sending data to the remote class and make sure we
     * recieve the proper response.
     *
     */
    public function testHandleLoadedClassAmf0()
    {
        // serialize the data to an AMF output stream
        $data[] = "12345";
        $this->_server->setClass('Zend_Amf_testclass');
        $newBody = new Zend_Amf_Value_MessageBody("Zend_Amf_testclass.test1","/1",$data);
        $request = new Zend_Amf_Request();
        $request->addAmfBody($newBody);
        $request->setObjectEncoding(0x00);
        $result = $this->_server->handle($request);
        $response = $this->_server->getResponse();
        $responseBody = $response->getAmfBodies();
        // Now check if the return data was properly set.
        $this->assertTrue(0 < count($responseBody), var_export($responseBody, 1));
        $this->assertTrue(array_key_exists(0, $responseBody), var_export($responseBody, 1));
        $this->assertEquals("String: 12345", $responseBody[0]->getData(), var_export($responseBody, 1));
    }

    public function testShouldAllowHandlingFunctionCallsViaAmf0()
    {
        // serialize the data to an AMF output stream
        $data = array('foo', 'bar');
        $this->_server->addFunction('Zend_Amf_Server_testFunction');
        $newBody = new Zend_Amf_Value_MessageBody("Zend_Amf_Server_testFunction","/1",$data);
        $request = new Zend_Amf_Request();
        $request->addAmfBody($newBody);
        $request->setObjectEncoding(0x00);
        $result = $this->_server->handle($request);
        $response = $this->_server->getResponse();
        $responseBody = $response->getAmfBodies();
        // Now check if the return data was properly set.
        $this->assertTrue(0 < count($responseBody), var_export($responseBody, 1));
        $this->assertTrue(array_key_exists(0, $responseBody), var_export($responseBody, 1));
        $this->assertEquals("bar: foo", $responseBody[0]->getData(), var_export($responseBody, 1));
    }

    /**
     * Test sending data to the remote class and make sure we
     * recieve the proper response.
     *
     */
    public function testHandleLoadedClassAmf3()
    {
        // serialize the data to an AMF output stream
        $data[] = "12345";
        $this->_server->setClass('Zend_Amf_testclass');
        // create a mock remoting message
        $message = new Zend_Amf_Value_Messaging_RemotingMessage();
        $message->operation = 'test1';
        $message->source = 'Zend_Amf_testclass';
        $message->body = $data;
        // create a mock message body to place th remoting message inside
        $newBody = new Zend_Amf_Value_MessageBody(null,"/1",$message);
        $request = new Zend_Amf_Request();
        // at the requested service to a request
        $request->addAmfBody($newBody);
        $request->setObjectEncoding(0x03);
        // let the server handle mock request
        $result = $this->_server->handle($request);
        $response = $this->_server->getResponse();
        $responseBody = $response->getAmfBodies();
        $this->assertTrue(0 < count($responseBody), var_export($responseBody, 1));
        $this->assertTrue(array_key_exists(0, $responseBody), var_export($responseBody, 1));
        // Now check if the return data was properly set.
        $acknowledgeMessage = $responseBody[0]->getData();
        // check that we have a message beening returned
        $this->assertTrue($acknowledgeMessage instanceof Zend_Amf_Value_Messaging_AcknowledgeMessage);
        // Check the message body is the expected data to be returned
        $this->assertEquals("String: 12345", $acknowledgeMessage->body);
    }

    /**
     * test command message. THis is the first call the Flex
     * makes before any subsequent service calls.
     */
    public function testCommandMessagePingOperation()
    {
        $message = new Zend_Amf_Value_Messaging_CommandMessage();
        $message->operation = 5;
        $message->messageId = $message->generateId();
        // create a mock message body to place th remoting message inside
        $newBody = new Zend_Amf_Value_MessageBody(null,"/1",$message);
        $request = new Zend_Amf_Request();
        // at the requested service to a request
        $request->addAmfBody($newBody);
        $request->setObjectEncoding(0x03);
        // let the server handle mock request
        $result = $this->_server->handle($request);
        $response = $this->_server->getResponse();
        $responseBody = $response->getAmfBodies();
        $this->assertTrue(0 < count($responseBody), var_export($responseBody, 1));
        $this->assertTrue(array_key_exists(0, $responseBody), var_export($responseBody, 1));
        // Now check if the return data was properly set.
        $acknowledgeMessage = $responseBody[0]->getData();
        // check that we have a message beening returned
        $this->assertTrue($acknowledgeMessage instanceof Zend_Amf_Value_Messaging_AcknowledgeMessage);
        // Check that the MessageID was not corrupeted when set to the correlationId
        $this->assertEquals($acknowledgeMessage->correlationId, $message->messageId);
    }

    public function testInvalidAmf0MessageShouldResultInErrorMessage()
    {
        // serialize the data to an AMF output stream
        $data[] = "12345";
        $this->_server->setClass('Zend_Amf_testclass');
        $newBody = new Zend_Amf_Value_MessageBody("Zend_Amf_testclass.bogus","/1",$data);
        $request = new Zend_Amf_Request();
        $request->addAmfBody($newBody);
        $request->setObjectEncoding(0x00);
        $result = $this->_server->handle($request);
        $bodies = $result->getAmfBodies();
        $found  = false;
        foreach ($bodies as $body) {
            $data = $body->getData();
            if (!is_array($data)) {
                continue;
            }
            if (!array_key_exists('description', $data)) {
                continue;
            }
            if (strstr($data['description'], 'does not exist')) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Invalid method did not raise error condition');
    }

    public function testInvalidCommandMessageShouldResultInErrorMessage()
    {
        $message = new Zend_Amf_Value_Messaging_CommandMessage();
        $message->operation = 'pong';
        $message->messageId = $message->generateId();

        // create a mock message body to place th remoting message inside
        $newBody = new Zend_Amf_Value_MessageBody(null,"/1",$message);
        $request = new Zend_Amf_Request();

        // at the requested service to a request
        $request->addAmfBody($newBody);
        $request->setObjectEncoding(0x03);

        // let the server handle mock request
        $result = $this->_server->handle($request);
        $response = $this->_server->getResponse();
        $responseBody = $response->getAmfBodies();
        $this->assertTrue(0 < count($responseBody), var_export($responseBody, 1));
        $this->assertTrue(array_key_exists(0, $responseBody), var_export($responseBody, 1));

        // Now check if the return data was properly set.
        $message = $responseBody[0]->getData();

        // check that we have a message beening returned
        $this->assertTrue($message instanceof Zend_Amf_Value_Messaging_ErrorMessage);
    }

    /**
     * Add a class mapping and lookup the mapping to make sure
     * the mapping succeeds
     */
    public function testClassMap()
    {
        $this->_server->setClassMap('controller.test', 'Zend_Amf_testclass');
        $className = Zend_Amf_Parse_TypeLoader::getMappedClassName('Zend_Amf_testclass');
        $this->assertEquals('controller.test', $className);
    }

    public function testDispatchingMethodShouldReturnErrorMessageForInvalidMethod()
    {
        // serialize the data to an AMF output stream
        $data[] = "12345";
        $this->_server->setClass('Zend_Amf_testclass');

        // create a mock remoting message
        $message = new Zend_Amf_Value_Messaging_RemotingMessage();
        $message->operation = 'bogus'; // INVALID method!
        $message->source    = 'Zend_Amf_testclass';
        $message->body      = $data;

        // create a mock message body to place th remoting message inside
        $newBody = new Zend_Amf_Value_MessageBody(null,"/1",$message);
        $request = new Zend_Amf_Request();

        // at the requested service to a request
        $request->addAmfBody($newBody);
        $request->setObjectEncoding(0x03);

        // let the server handle mock request
        $result = $this->_server->handle($request);
        $bodies = $result->getAmfBodies();
        $found  = false;
        foreach ($bodies as $body) {
            $data = $body->getData();
            if ($data instanceof Zend_Amf_Value_Messaging_ErrorMessage) {
                if (strstr($data->faultString, 'does not exist')) {
                    $found = true;
                    break;
                }
            }
        }
        $this->assertTrue($found, 'Invalid method did not raise error condition');
    }

    public function testDispatchingMethodThatThrowsExceptionShouldReturnErrorMessageWhenProductionFlagOff()
    {
        // serialize the data to an AMF output stream
        $data = array();
        $this->_server->setClass('Zend_Amf_testclass');

        // create a mock remoting message
        $message = new Zend_Amf_Value_Messaging_RemotingMessage();
        $message->operation = 'throwException';
        $message->source    = 'Zend_Amf_testclass';
        $message->body      = $data;

        // create a mock message body to place th remoting message inside
        $newBody = new Zend_Amf_Value_MessageBody(null,"/1",$message);
        $request = new Zend_Amf_Request();

        // at the requested service to a request
        $request->addAmfBody($newBody);
        $request->setObjectEncoding(0x03);

        // let the server handle mock request
        $result = $this->_server->handle($request);
        $bodies = $result->getAmfBodies();
        $found  = false;
        foreach ($bodies as $body) {
            $data = $body->getData();
            if ($data instanceof Zend_Amf_Value_Messaging_ErrorMessage) {
                if (strstr($data->faultString, 'should not be displayed')) {
                    $found = true;
                    break;
                }
            }
        }
        $this->assertTrue($found, 'Method raising exception should display error message when not in production');
    }

    public function testDispatchingMethodThatThrowsExceptionShouldNotReturnErrorMessageWhenProductionFlagOn()
    {
        // serialize the data to an AMF output stream
        $data = array();
        $this->_server->setClass('Zend_Amf_testclass')
                      ->setProduction(true);

        // create a mock remoting message
        $message = new Zend_Amf_Value_Messaging_RemotingMessage();
        $message->operation = 'throwException';
        $message->source    = 'Zend_Amf_testclass';
        $message->body      = $data;

        // create a mock message body to place th remoting message inside
        $newBody = new Zend_Amf_Value_MessageBody(null,"/1",$message);
        $request = new Zend_Amf_Request();

        // at the requested service to a request
        $request->addAmfBody($newBody);
        $request->setObjectEncoding(0x03);

        // let the server handle mock request
        $result = $this->_server->handle($request);
        $bodies = $result->getAmfBodies();
        $found  = false;
        foreach ($bodies as $body) {
            $data = $body->getData();
            if ($data instanceof Zend_Amf_Value_Messaging_ErrorMessage) {
                if (strstr($data->faultString, 'should not be displayed')) {
                    $found = true;
                    break;
                }
            }
        }
        $this->assertFalse($found, 'Method raising exception should not display error message when in production');
    }

    public function testDispatchingMethodShouldPassInvocationArgumentsToMethod()
    {
        // serialize the data to an AMF output stream
        $data[] = "baz";
        $this->_server->setClass('Zend_Amf_testclass', '', 'foo', 'bar');

        // create a mock remoting message
        $message = new Zend_Amf_Value_Messaging_RemotingMessage();
        $message->operation = 'checkArgv';
        $message->source    = 'Zend_Amf_testclass';
        $message->body      = $data;

        // create a mock message body to place th remoting message inside
        $newBody = new Zend_Amf_Value_MessageBody(null, "/1" ,$message);
        $request = new Zend_Amf_Request();

        // at the requested service to a request
        $request->addAmfBody($newBody);
        $request->setObjectEncoding(0x03);

        // let the server handle mock request
        $result = $this->_server->handle($request);
        $bodies = $result->getAmfBodies();
        $found  = false;
        foreach ($bodies as $body) {
            $data  = $body->getData();
            if ('Zend_Amf_Value_Messaging_AcknowledgeMessage' == get_class($data)) {
                if ('baz:foo:bar' == $data->body) {
                    $found = true;
                    break;
                }
            }
        }
        $this->assertTrue($found, 'Valid response not found');
    }

    public function testServerShouldSeamlesslyInvokeStaticMethods()
    {
        // serialize the data to an AMF output stream
        $data[] = "testing";
        $this->_server->setClass('Zend_Amf_testclass');

        // create a mock remoting message
        $message = new Zend_Amf_Value_Messaging_RemotingMessage();
        $message->operation = 'checkStaticUsage';
        $message->source    = 'Zend_Amf_testclass';
        $message->body      = $data;

        // create a mock message body to place th remoting message inside
        $newBody = new Zend_Amf_Value_MessageBody(null, "/1" ,$message);
        $request = new Zend_Amf_Request();

        // at the requested service to a request
        $request->addAmfBody($newBody);
        $request->setObjectEncoding(0x03);

        // let the server handle mock request
        $result = $this->_server->handle($request);
        $bodies = $result->getAmfBodies();
        $found  = false;
        foreach ($bodies as $body) {
            $data  = $body->getData();
            if ('Zend_Amf_Value_Messaging_AcknowledgeMessage' == get_class($data)) {
                if ('testing' == $data->body) {
                    $found = true;
                    break;
                }
            }
        }
        $this->assertTrue($found, 'Valid response not found');
    }

    public function testServerShouldSeamlesslyInvokeFunctions()
    {
        // serialize the data to an AMF output stream
        $data[] = 'foo';
        $data[] = 'bar';
        $this->_server->addFunction('Zend_Amf_Server_testFunction');

        // create a mock remoting message
        $message = new Zend_Amf_Value_Messaging_RemotingMessage();
        $message->operation = 'Zend_Amf_Server_testFunction';
        $message->source    = null;
        $message->body      = $data;

        // create a mock message body to place th remoting message inside
        $newBody = new Zend_Amf_Value_MessageBody(null, "/1" ,$message);
        $request = new Zend_Amf_Request();

        // at the requested service to a request
        $request->addAmfBody($newBody);
        $request->setObjectEncoding(0x03);

        // let the server handle mock request
        $result = $this->_server->handle($request);
        $bodies = $result->getAmfBodies();
        $found  = false;
        foreach ($bodies as $body) {
            $data  = $body->getData();
            if ('Zend_Amf_Value_Messaging_AcknowledgeMessage' == get_class($data)) {
                if ('bar: foo' == $data->body) {
                    $found = true;
                    break;
                }
            }
        }
        $this->assertTrue($found, 'Valid response not found');
    }

    public function testDispatchingMethodCorrespondingToClassWithPrivateConstructorShouldReturnErrorMessage()
    {
        // serialize the data to an AMF output stream
        $data[] = "baz";
        $this->_server->setClass('Zend_Amf_testclassPrivate');

        // create a mock remoting message
        $message = new Zend_Amf_Value_Messaging_RemotingMessage();
        $message->operation = 'test1';
        $message->source    = 'Zend_Amf_testclassPrivate';
        $message->body      = $data;

        // create a mock message body to place th remoting message inside
        $newBody = new Zend_Amf_Value_MessageBody(null, "/1" ,$message);
        $request = new Zend_Amf_Request();

        // at the requested service to a request
        $request->addAmfBody($newBody);
        $request->setObjectEncoding(0x03);

        // let the server handle mock request
        $result = $this->_server->handle($request);
        $bodies = $result->getAmfBodies();
        $found  = false;
        foreach ($bodies as $body) {
            $data  = $body->getData();
            if ('Zend_Amf_Value_Messaging_ErrorMessage' == get_class($data)) {
                if (strstr($data->faultString, 'Error instantiating class')) {
                    $found = true;
                    break;
                }
            }
        }
        $this->assertTrue($found, 'Method succeeded?');
    }

    public function testNotPassingRequestToHandleShouldResultInServerCreatingRequest()
    {
        $this->_server->setClass('Zend_Amf_testclass');
        ob_start();
        $result  = $this->_server->handle();
        $content = ob_get_clean();
        $request = $this->_server->getRequest();
        $this->assertTrue($request instanceof Zend_Amf_Request_Http);
        $bodies  = $request->getAmfBodies();
        $this->assertEquals(0, count($bodies));
        $this->assertContains('Endpoint', $content);
    }

    public function testSetRequestShouldAllowValidStringClassNames()
    {
        $this->_server->setRequest('Zend_Amf_Request');
        $request = $this->_server->getRequest();
        $this->assertTrue($request instanceof Zend_Amf_Request);
        $this->assertFalse($request instanceof Zend_Amf_Request_Http);
    }

    /**
     * @expectedException Zend_Amf_Server_Exception
     */
    public function testSetRequestShouldRaiseExceptionOnInvalidStringClassName()
    {
        $this->_server->setRequest('Zend_Amf_ServerTest_BogusRequest');
    }

    public function testSetRequestShouldAllowValidRequestObjects()
    {
        $request = new Zend_Amf_Request;
        $this->_server->setRequest($request);
        $this->assertSame($request, $this->_server->getRequest());
    }

    /**
     * @expectedException Zend_Amf_Server_Exception
     */
    public function testSetRequestShouldRaiseExceptionOnInvalidRequestObjects()
    {
        require_once 'Zend/XmlRpc/Request.php';
        $request = new Zend_XmlRpc_Request;
        $this->_server->setRequest($request);
    }

    public function testSetResponseShouldAllowValidStringClassNames()
    {
        $this->_server->setResponse('Zend_Amf_Response');
        $response = $this->_server->getResponse();
        $this->assertTrue($response instanceof Zend_Amf_Response);
        $this->assertFalse($response instanceof Zend_Amf_Response_Http);
    }

    /**
     * @expectedException Zend_Amf_Server_Exception
     */
    public function testSetResponseShouldRaiseExceptionOnInvalidStringClassName()
    {
        $this->_server->setResponse('Zend_Amf_ServerTest_BogusResponse');
    }

    public function testSetResponseShouldAllowValidResponseObjects()
    {
        $response = new Zend_Amf_Response;
        $this->_server->setResponse($response);
        $this->assertSame($response, $this->_server->getResponse());
    }

    /**
     * @expectedException Zend_Amf_Server_Exception
     */
    public function testSetResponseShouldRaiseExceptionOnInvalidResponseObjects()
    {
        require_once 'Zend/XmlRpc/Response.php';
        $response = new Zend_XmlRpc_Response;
        $this->_server->setResponse($response);
    }

    public function testGetFunctionsShouldReturnArrayOfDispatchables()
    {
        $this->_server->addFunction('Zend_Amf_Server_testFunction', 'tf')
                      ->setClass('Zend_Amf_testclass', 'tc')
                      ->setClass('Zend_Amf_testclassPrivate', 'tcp');
        $functions = $this->_server->getFunctions();
        $this->assertTrue(is_array($functions));
        $this->assertTrue(0 < count($functions));
        $namespaces = array('tf', 'tc', 'tcp');
        foreach ($functions as $key => $value) {
            $this->assertTrue(strstr($key, '.') ? true : false, $key);
            $ns = substr($key, 0, strpos($key, '.'));
            $this->assertContains($ns, $namespaces, $key);
            $this->assertTrue($value instanceof Zend_Server_Reflection_Function_Abstract);
        }
    }

    public function testFaultShouldBeUnimplemented()
    {
        $this->assertNull($this->_server->fault());
    }

    public function testPersistenceShouldBeUnimplemented()
    {
        $this->assertNull($this->_server->setPersistence(true));
    }

    public function testLoadFunctionsShouldBeUnimplemented()
    {
        $this->assertNull($this->_server->loadFunctions(true));
    }
}

if (PHPUnit_MAIN_METHOD == "Zend_Amf_ServerTest::main") {
    Zend_Amf_ServerTest::main();
}

/**
 * Zend_Amf_Server_testFunction
 *
 * Function for use with Amf server unit tests
 *
 * @param array $var1
 * @param string $var2
 * @return string
 */
function Zend_Amf_Server_testFunction($var1, $var2 = 'optional')
{
    return $var2 . ': ' . implode(',', (array) $var1);
}

/**
 * Zend_Amf_Server_testFunction2
 *
 * Function for use with Amf server unit tests
 *
 * @return string
 */
function Zend_Amf_Server_testFunction2()
{
    return 'function2';
}

/**
 * Class to used with Zend_Amf_Server unit tests.
 *
 */
class Zend_Amf_testclass
{
    public function __construct()
    {
    }

     /**
     * Test1
     *
     * Returns 'String: ' . $string
     *
     * @param string $string
     * @return string
     */
    public function test1($string = '')
    {
        return 'String: '. (string) $string;
    }

    /**
     * Test2
     *
     * Returns imploded array
     *
     * @param array $array
     * @return string
     */
    public static function test2($array)
    {
        return implode('; ', (array) $array);
    }

    /**
     * Test3
     *
     * Should not be available...
     *
     * @return void
     */
    protected function _test3()
    {
    }

    /**
     * Test base64 encoding in request and response
     *
     * @param  base64 $data
     * @return base64
     */
    public function base64($data)
    {
        return $data;
    }

    /**
     * Test that invoke arguments are passed
     * 
     * @param  string $message message argument for comparisons
     * @return string
     */
    public function checkArgv($message)
    {
        $argv = func_get_args();
        return implode(':', $argv);
    }

    /**
     * Test static usage
     * 
     * @param  string $message 
     * @return string
     */
    public static function checkStaticUsage($message)
    {
        return $message;
    }

    /**
     * Test throwing exceptions 
     * 
     * @return void
     */
    public function throwException()
    {
        throw new Exception('This exception should not be displayed');
    }
}

/**
 * Class with private constructor
 */
class Zend_Amf_testclassPrivate
{
    private function __construct()
    {
    }

     /**
     * Test1
     *
     * Returns 'String: ' . $string
     *
     * @param string $string
     * @return string
     */
    public function test1($string = '')
    {
        return 'String: '. (string) $string;
    }
}
