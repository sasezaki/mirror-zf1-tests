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
 * @package    Zend_XmlRpc
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version $Id: ValueTest.php 18442 2009-09-30 13:17:48Z lars $
 */
abstract class Zend_XmlRpc_TestProvider
{
    public static function provideGenerators()
    {
        return array(
            array(new Zend_XmlRpc_Generator_DOMDocument()),
            array(new Zend_XmlRpc_Generator_XMLWriter()),
        );
    }

    public static function provideGeneratorsWithAlternateEncodings()
    {
        return array(
            array(new Zend_XmlRpc_Generator_DOMDocument('ISO-8859-1')),
            array(new Zend_XmlRpc_Generator_XMLWriter('ISO-8859-1')),
        );
    }
}
