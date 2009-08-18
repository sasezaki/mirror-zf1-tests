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
 * @version    $Id$
 */

require_once 'Zend/Db/Statement/TestCommon.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);

/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Db
 * @group      Zend_Db_Statement
 */
class Zend_Db_Statement_SqlsrvTest extends Zend_Db_Statement_TestCommon
{
    // http://msdn.microsoft.com/en-us/library/cc296197(SQL.90).aspx
    protected $_getColumnMetaKeys = array(
        'Name' , 'Type', 'Size', 'Precision', 'Scale', 'Nullable'
    );

    public function testStatementExecuteWithParams()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');

        // Make IDENTITY column accept explicit value.
        // This can be done in only one table in a given session.
        sqlsrv_query($this->_db->getConnection(), "SET IDENTITY_INSERT $products ON");
        parent::testStatementExecuteWithParams();
        sqlsrv_query($this->_db->getConnection(), "SET IDENTITY_INSERT $products OFF");
    }

    public function testStatementBindParamByName()
    {
        $this->markTestSkipped($this->getDriver() . ' does not support bind by name.');
    }

    public function testStatementBindValueByName()
    {
        $this->markTestSkipped($this->getDriver() . ' does not support bind by name.');
    }

    public function testStatementBindParamByPosition()
    {
        $this->markTestSkipped($this->getDriver() . ' does not support bind by position.');
    }

    public function testStatementBindValueByPosition()
    {
        $this->markTestSkipped($this->getDriver() . ' does not support bind by position.');
    }

    public function testStatementNextRowset()
    {
        $products   = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $query = "SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC";
        $stmt  = $this->_db->query($query . ';' . $query);

        $result1 = $stmt->fetchAll();

        $stmt->nextRowset();

        $result2 = $stmt->fetchAll();

        $this->assertEquals(count($result1), count($result2));
        $this->assertEquals($result1, $result2);

        $stmt->closeCursor();
    }

    public function testStatementErrorInfo()
    {
        $products   = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $query = "INVALID SELECT * FROM INVALID TABLE WHERE $product_id > 1 ORDER BY $product_id ASC";
        $stmt  = new Zend_Db_Statement_Sqlsrv($this->_db, $query);

        try {
            $stmt->fetchAll();
            $this->fail("Invalid query should have throw an error");
        } catch (Zend_Db_Statement_Sqlsrv_Exception $e) {
            // Exception is thrown, nothing to worry about
            $this->assertEquals(-11, $e->getCode());
        }

        $this->assertNotSame(false, $stmt->errorCode());
        $this->assertEquals(-11, $stmt->errorCode());

        $errors = $stmt->errorInfo();
        $this->assertEquals(2, count($errors));
        $this->assertEquals($stmt->errorCode(), $errors[0]);
        $this->assertType('string', $errors[1]);
    }

    public function getDriver()
    {
        return 'Sqlsrv';
    }
}
