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
 * @package    Zend_Http
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Http_UserAgent_AllTests::main');
}

require_once 'Zend/Http/UserAgentTest.php';
require_once 'Zend/Http/UserAgent/AbstractDeviceTest.php';
require_once 'Zend/Http/UserAgent/Features/Adapter/WurflApiTest.php';

/**
 * @category   Zend
 * @package    Zend_Http
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Http
 */
class Zend_Http_UserAgent_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_Http - UserAgent');

        $suite->addTestSuite('Zend_Http_UserAgentTest');
        $suite->addTestSuite('Zend_Http_UserAgent_AbstractDeviceTest');
        $suite->addTestSuite('Zend_Http_UserAgent_Features_Adapter_WurflApiTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Http_UserAgent_AllTests::main') {
    Zend_Http_UserAgent_AllTests::main();
}
