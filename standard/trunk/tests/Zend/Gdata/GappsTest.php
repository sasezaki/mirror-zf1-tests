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
 * @package    Zend_Gdata
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/TestHelper.php';
require_once 'Zend/Gdata/Gapps.php';
require_once 'Zend/Gdata/ClientLogin.php';
require_once 'Zend/Http/Client.php';

/**
 * @package Zend_Gdata
 * @subpackage UnitTests
 */
class Zend_Gdata_GappsTest extends PHPUnit_Framework_TestCase
{
    const TEST_DOMAIN = 'nowhere.invalid';

    public function setUp()
    {
        // These tests shouldn't be doing anything online, so we can use
        // bogous auth credentials.
        $this->gdata = new Zend_Gdata_Gapps(null, self::TEST_DOMAIN);
    }

    public function testMagicFactoryProvidesQueriesWithDomains() {
        $userQ = $this->gdata->newUserQuery();
        $this->assertTrue($userQ instanceof Zend_Gdata_Gapps_UserQuery);
        $this->assertEquals(self::TEST_DOMAIN, $userQ->getDomain());
        $this->assertEquals(null, $userQ->getUsername());

        $userQ = $this->gdata->newUserQuery('foo');
        $this->assertTrue($userQ instanceof Zend_Gdata_Gapps_UserQuery);
        $this->assertEquals(self::TEST_DOMAIN, $userQ->getDomain());
        $this->assertEquals('foo', $userQ->getUsername());
    }

    public function testMagicFactoryLeavesNonQueriesAlone() {
        $login = $this->gdata->newLogin('blah');
        $this->assertTrue($login instanceof Zend_Gdata_Gapps_Extension_Login);
        $this->assertEquals('blah', $login->username);
    }

}