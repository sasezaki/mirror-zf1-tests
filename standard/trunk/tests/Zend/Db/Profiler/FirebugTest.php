<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: NullTest.php 3980 2007-03-15 21:38:38Z mike $
 */

/** PHPUnit_Framework_TestCase */
require_once 'PHPUnit/Framework/TestCase.php';

/** Zend_Db */
require_once 'Zend/Db.php';

/** Zend_Db_Profiler_Firebug */
require_once 'Zend/Db/Profiler/Firebug.php';

/** Zend_Wildfire_Plugin_FirePhp */
require_once 'Zend/Wildfire/Plugin/FirePhp.php';

/** Zend_Wildfire_Channel_HttpHeaders */
require_once 'Zend/Wildfire/Channel/HttpHeaders.php';

/** Zend_Controller_Request_Http */
require_once 'Zend/Controller/Request/Http.php';

/** Zend_Controller_Response_Http */
require_once 'Zend/Controller/Response/Http.php';


/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: NullTest.php 3980 2007-03-15 21:38:38Z mike $
 */
class Zend_Db_Profiler_FirebugTest extends PHPUnit_Framework_TestCase
{

    protected $_controller = null;
    protected $_request = null;
    protected $_response = null;
    protected $_profiler = null;
    protected $_db = null;

    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Zend_Db_Profiler_FirebugTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp()
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markAsSkipped('Requires PDO_Sqlite extension');
        }

        date_default_timezone_set('America/Los_Angeles');

        $this->_request = new Zend_Db_Profiler_FirebugTest_Request();
        $this->_response = new Zend_Controller_Response_Http();

        $channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        $channel->setRequest($this->_request);
        $channel->setResponse($this->_response);

        $this->_profiler = new Zend_Db_Profiler_Firebug();
        $this->_db = Zend_Db::factory('PDO_SQLITE',
                               array('dbname' => ':memory:',
                                     'profiler' => $this->_profiler));
        $this->_db->getConnection()->exec('CREATE TABLE foo (
                                              id      INTEGNER NOT NULL,
                                              col1    VARCHAR(10) NOT NULL
                                            )');
    }

    public function tearDown()
    {
        $this->_db->getConnection()->exec('DROP TABLE foo');

        Zend_Wildfire_Channel_HttpHeaders::destroyInstance();
        Zend_Wildfire_Plugin_FirePhp::destroyInstance();
    }

    public function testEnable()
    {
        $channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        $protocol = $channel->getProtocol(Zend_Wildfire_Plugin_FirePhp::PROTOCOL_URI);

        $this->_db->insert('foo', array('id'=>1,'col1'=>'original'));

        Zend_Wildfire_Channel_HttpHeaders::getInstance()->flush();

        $this->assertFalse($protocol->getMessages());

        $this->_profiler->setEnabled(true);

        $this->_db->insert('foo', array('id'=>1,'col1'=>'original'));

        Zend_Wildfire_Channel_HttpHeaders::getInstance()->flush();

        $messages = $protocol->getMessages();

        $this->assertEquals(substr($messages[Zend_Wildfire_Plugin_FirePhp::STRUCTURE_URI_FIREBUGCONSOLE]
                                            [Zend_Wildfire_Plugin_FirePhp::PLUGIN_URI][0],0,44),
                            '[{"Type":"TABLE"},["Zend_Db_Profiler_Firebug');
    }

    public function testCustomLabel()
    {
        $channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        $protocol = $channel->getProtocol(Zend_Wildfire_Plugin_FirePhp::PROTOCOL_URI);

        $this->_profiler = new Zend_Db_Profiler_Firebug('Label 1');
        $this->_profiler->setEnabled(true);
        $this->_db->setProfiler($this->_profiler);
        $this->_db->insert('foo', array('id'=>1,'col1'=>'original'));

        Zend_Wildfire_Channel_HttpHeaders::getInstance()->flush();

        $messages = $protocol->getMessages();

        $this->assertEquals(substr($messages[Zend_Wildfire_Plugin_FirePhp::STRUCTURE_URI_FIREBUGCONSOLE]
                                            [Zend_Wildfire_Plugin_FirePhp::PLUGIN_URI][0],0,27),
                            '[{"Type":"TABLE"},["Label 1');
    }

}


class Zend_Db_Profiler_FirebugTest_Request extends Zend_Controller_Request_Http
{
    public function getHeader($header)
    {
        if ($header == 'User-Agent') {
            return 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.1.14) Gecko/20080404 Firefox/2.0.0.14 FirePHP/0.1.0';
        }
    }
}

