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
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Entry.php 3941 2007-03-14 21:36:13Z darby $
 */

/**
 * @see Zend_Gdata_Entry
 */
require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Extension
 */
require_once 'Zend/Gdata/Extension.php';


/**
 * Concrete class for working with colCount elements.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Spreadsheets_Extension_ColCount extends Zend_Gdata_Extension
{

    protected $_rootElement = 'colCount';
    protected $_rootNamespace = 'gs';

    /**
     * Constructs a new Zend_Gdata_Spreadsheets_Extension_ColCount element.
     * @param string $text (optional) Text contents of the element.
     */
    public function __construct($text = null)
    {
        foreach (Zend_Gdata_Spreadsheets::$namespaces as $nsPrefix => $nsUri) {
            $this->registerNamespace($nsPrefix, $nsUri);
        }
        parent::__construct();
        $this->_text = $text;
    }
}
