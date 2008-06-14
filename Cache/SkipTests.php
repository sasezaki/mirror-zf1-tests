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
 * @package    Zend_Cache
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: BasicSqliteTest.php 4194 2007-03-22 23:50:34Z darby $
 */


/**
 * PHPUnit_Framework_TestCase
 */
require_once 'PHPUnit/Framework/TestCase.php';

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);


/**
 * @category   Zend
 * @package    Zend_Cache
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Cache_BackendTest_SkipTests extends PHPUnit_Framework_TestCase
{

    public $message = 'Skipped for unspecified reason';

    public function setUp()
    {
        $this->markTestSkipped($this->message);
    }

    public function testCacheBackend()
    {
        // this is here only so we have at least one test
    }

}

class Zend_Cache_ApcBackendTest_SkipTests extends Zend_Cache_BackendTest_SkipTests
{
}

class Zend_Cache_XcacheBackendTest_SkipTests extends Zend_Cache_BackendTest_SkipTests
{
}

class Zend_Cache_MemcachedBackendTest_SkipTests extends Zend_Cache_BackendTest_SkipTests
{
}

class Zend_Cache_SqliteBackendTest_SkipTests extends Zend_Cache_BackendTest_SkipTests
{
}

class Zend_Cache_ZendPlatformBackendTest_SkipTests extends Zend_Cache_BackendTest_SkipTests
{
}
