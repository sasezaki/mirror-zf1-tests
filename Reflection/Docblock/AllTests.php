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
 * @package    Zend_Reflection
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Reflection_Docblock_AllTests::main');
}

require_once 'Zend/Reflection/Docblock/TagTest.php';
require_once 'Zend/Reflection/Docblock/Tag/ParamTest.php';
require_once 'Zend/Reflection/Docblock/Tag/ReturnTest.php';

/**
 * @category   Zend
 * @package    Zend_Reflection
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Reflection
 */
class Zend_Reflection_Docblock_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_Reflection_Docblock');

        $suite->addTestSuite('Zend_Reflection_Docblock_TagTest');
        $suite->addTestSuite('Zend_Reflection_Docblock_Tag_ParamTest');
        $suite->addTestSuite('Zend_Reflection_Docblock_Tag_ReturnTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Reflection_Docblock_AllTests::main') {
    Zend_Reflection_Docblock_AllTests::main();
}
