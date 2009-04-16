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
 * @package    Zend_Application
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Application_Resource_NavigationTest::main');
}

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../../../TestHelper.php';

/**
 * Zend_Loader_Autoloader
 */
require_once 'Zend/Loader/Autoloader.php';

/**
 * @category   Zend
 * @package    Zend_Application
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Application_Resource_NavigationTest extends PHPUnit_Framework_TestCase
{
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite(__CLASS__);
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp()
    {
        // Store original autoloaders
        $this->loaders = spl_autoload_functions();
        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no 
            // autoloaders registered...
            $this->loaders = array();
        }

        Zend_Loader_Autoloader::resetInstance();
        $this->autoloader = Zend_Loader_Autoloader::getInstance();

        $this->application = new Zend_Application('testing');

        $this->bootstrap = new Zend_Application_Bootstrap_Bootstrap($this->application);

        Zend_Registry::_unsetInstance();
    }

    public function tearDown()
    {
        // Restore original autoloaders
        $loaders = spl_autoload_functions();
        foreach ($loaders as $loader) {
            spl_autoload_unregister($loader);
        }

        foreach ($this->loaders as $loader) {
            spl_autoload_register($loader);
        }
    }

    public function testInitializationInitializesNavigationObject()
    {
		$this->bootstrap->registerPluginResource('view');
        $resource = new Zend_Application_Resource_Navigation(array());
        $resource->setBootstrap($this->bootstrap); 
        $resource->init();
        $this->assertTrue($resource->getContainer() instanceof Zend_Navigation_Container);
		$this->bootstrap->unregisterPluginResource('view');
    }

    public function testInitializationReturnsNavigationObject()
    {
   		$this->bootstrap->registerPluginResource('view');
        $resource = new Zend_Application_Resource_Navigation(array());
        $resource->setBootstrap($this->bootstrap);
        $test = $resource->init();
        $this->assertTrue($test instanceof Zend_Navigation);
		$this->bootstrap->unregisterPluginResource('view');
    }
    
    public function testContainerIsStoredInViewhelper()
    {
   		$options = array('pages'=> array(new Zend_Navigation_Page_Mvc(array(
		    'action'     => 'index',
		    'controller' => 'index'))));
   		
    	$this->bootstrap->registerPluginResource('view');
        $resource = new Zend_Application_Resource_Navigation($options);
        $resource->setBootstrap($this->bootstrap)->init();
                
        $view = $this->bootstrap->getPluginResource('view')->getView();
        $number = $view->getHelper('navigation')->navigation()->count();
        
        $this->assertEquals($number,1);
		$this->bootstrap->unregisterPluginResource('view');
    }
        
    public function testContainerIsStoredInRegistry()
    {
   		$options = array('pages'=> array(new Zend_Navigation_Page_Mvc(array(
		    'action'     => 'index',
		    'controller' => 'index'))), 'storage' => array('registry' => true));
   		
        $resource = new Zend_Application_Resource_Navigation($options);
        $resource->setBootstrap($this->bootstrap)->init();
                
        $key = Zend_Application_Resource_Navigation::DEFAULT_REGISTRY_KEY;
		$this->assertEquals(Zend_Registry::isRegistered($key),true);
		$container = Zend_Registry::get($key);
        $number = $container->count();
        
        $this->assertEquals($number,1);
		$this->bootstrap->unregisterPluginResource('view');
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Application_Resource_LocaleTest::main') {
    Zend_Application_Resource_LocaleTest::main();
}
