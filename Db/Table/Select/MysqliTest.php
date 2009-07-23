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
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Db/Table/Select/TestCommon.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);

class Zend_Db_Table_Select_MysqliTest extends Zend_Db_Table_Select_TestCommon
{

    public function getDriver()
    {
        return 'Mysqli';
    }

    /**
     * ZF-2017: Test bind use of the Zend_Db_Select class.
     * @group ZF-2017
     */
    public function testSelectQueryWithBinds()
    {
        $this->markTestSkipped($this->getDriver() . ' does not support named parameters');
    }
}