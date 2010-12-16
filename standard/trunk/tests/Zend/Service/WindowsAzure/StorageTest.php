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
 * @package    Zend_Service_WindowsAzure
 * @subpackage UnitTests
 * @version    $Id$
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Service_WindowsAzure_Storage */
require_once 'Zend/Service/WindowsAzure/Storage.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage UnitTests
 * @version    $Id$
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_StorageTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test constructor for devstore
     */
    public function testConstructorForDevstore()
    {
        $storage = new Zend_Service_WindowsAzure_Storage();
        $this->assertEquals('http://127.0.0.1:10000/devstoreaccount1', $storage->getBaseUrl());
    }

    /**
     * Test constructor for production
     */
    public function testConstructorForProduction()
    {
        $storage = new Zend_Service_WindowsAzure_Storage(Zend_Service_WindowsAzure_Storage::URL_CLOUD_BLOB, 'testing', '');
        $this->assertEquals('http://testing.blob.core.windows.net', $storage->getBaseUrl());
    }
}

