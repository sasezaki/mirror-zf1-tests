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
 * @package    Zend_Markup
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';

if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Zend_Markup_BbcodeAndHtmlTest::main");
}

require_once 'Zend/Markup.php';
require_once 'Zend/Filter/StringToUpper.php';

/**
 * @category   Zend
 * @package    Zend_Markup
 * @subpackage UnitTests
 * @group      Zend_Markup
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Markup_BbcodeAndHtmlTest extends PHPUnit_Framework_TestCase
{

    /**
     * Zend_Markup_Renderer_RendererAbstract instance
     *
     * @var Zend_Markup_Renderer_RendererAbstract
     */
    protected $_markup;


    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Zend_Markup_MarkupTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->_markup = Zend_Markup::factory('bbcode', 'html');
    }

    /**
     * Tears down the fixture
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->_markup);
    }

    /**
     * Test for basic tags
     *
     * @return void
     */
    public function testBasicTags()
    {
        $this->assertEquals('<strong>foo</strong>bar', $this->_markup->render('[b]foo[/b]bar'));
        $this->assertEquals('<strong>foo<em>bar</em>foo</strong>ba[r',
            $this->_markup->render('[b=test file="test"]foo[i hell=nice]bar[/i]foo[/b]ba[r'));
    }

    /**
     * Test the behaviour of complicated tags
     *
     * @return void
     */
    public function testComplicatedTags()
    {
        $this->assertEquals('<a href="http://framework.zend.com/">http://framework.zend.com/</a>',
            $this->_markup->render('[url]http://framework.zend.com/[/url]'));
        $this->assertEquals('<a href="http://framework.zend.com/">foo</a>',
            $this->_markup->render('[url=http://framework.zend.com/]foo[/url]'));
        $this->assertEquals('bar', $this->_markup->render('[url="invalid"]bar[/url]'));

        $this->assertEquals('<img src="http://framework.zend.com/images/logo.png" alt="logo" />',
            $this->_markup->render('[img]http://framework.zend.com/images/logo.png[/img]'));
        $this->assertEquals('<img src="http://framework.zend.com/images/logo.png" alt="Zend Framework" />',
            $this->_markup->render('[img alt="Zend Framework"]http://framework.zend.com/images/logo.png[/img]'));
        $this->assertEquals('invalid', $this->_markup->render('[img]invalid[/img]'));

    }

    /**
     * Test input exceptions
     *
     * @return void
     */
    public function testExceptionParserWrongInputType()
    {
        $this->setExpectedException('Zend_Markup_Parser_Exception');

        $this->_markup->getParser()->parse(array());
    }

    /**
     * Test exception
     *
     * @return void
     */
    public function testExceptionParserEmptyInput()
    {
        $this->setExpectedException('Zend_Markup_Parser_Exception');

        $this->_markup->getParser()->parse('');
    }

    /**
     * Test adding tags
     *
     * @return void
     */
    public function testAddTags()
    {
        $this->_markup->getPluginLoader()->addPrefixPath(
            'Zend_Markup_Test_Renderer_Html',
            'Zend/Markup/Test/Renderer/Html'
        );

        $this->_markup->addTag('bar',
            Zend_Markup_Renderer_RendererAbstract::TYPE_CALLBACK | Zend_Markup_Renderer_RendererAbstract::TAG_NORMAL,
            array('group' => 'inline'));
        $this->_markup->addTag('suppp',
            Zend_Markup_Renderer_RendererAbstract::TYPE_REPLACE | Zend_Markup_Renderer_RendererAbstract::TAG_NORMAL,
            array('start' => '<sup>', 'end' => '</sup>', 'group' => 'inline'));
        $this->_markup->addTag('zend',
            Zend_Markup_Renderer_RendererAbstract::TYPE_REPLACE | Zend_Markup_Renderer_RendererAbstract::TAG_SINGLE,
            array('replace' => 'Zend Framework', 'group' => 'inline'));
        $this->_markup->addTag('line', Zend_Markup_Renderer_RendererAbstract::TYPE_ALIAS,
            array('name' => 'hr'));

        $this->assertEquals('[foo=blaat]hell<sup>test</sup>blaat[/foo]',
            $this->_markup->render('[bar="blaat"]hell[suppp]test[/suppp]blaat[/]'));

        $this->assertEquals('Zend Framework', $this->_markup->render('[zend]'));
        $this->assertEquals('<hr />', $this->_markup->render('[line]'));

        $this->assertEquals('<sup>test aap</sup>test',
            $this->_markup->render('[suppp]test aap[/suppp]test'));
    }

    public function testHtmlUrlTitleIsRenderedCorrectly() {
        $this->assertEquals('<a href="http://exampl.com" title="foo">test</a>',
            $this->_markup->render('[url=http://exampl.com title=foo]test[/url]'));
    }

    public function testValueLessAttributeDoesNotThrowNotice() {
        // Notice: Uninitialized string offset: 42
        // in Zend/Markup/Parser/Bbcode.php on line 316
        $expected = '<a href="http://example.com">Example</a>';
        $value    = '[url=http://example.com foo]Example[/url]';
        $this->assertEquals($expected, $this->_markup->render($value));
    }

    public function testAttributeNotEndingValueDoesNotThrowNotice()
    {
        // Notice: Uninitialized string offset: 13
        // in Zend/Markup/Parser/Bbcode.php on line 337

        $this->_markup->render('[url=http://framework.zend.com/ title="');
    }

    public function testAttributeFollowingValueDoesNotThrowNotice()
    {
        // Notice: Uninitialized string offset: 38
        // in Zend/Markup/Parser/Bbcode.php on line 337

        $this->_markup->render('[url="http://framework.zend.com/"title');
    }

    public function testHrTagWorks() {
        $this->assertEquals('foo<hr />bar', $this->_markup->render('foo[hr]bar'));
    }

    public function testFunkyCombos() {
        $expected = '<span style="text-decoration: underline;">a[/b][hr]b'
                  . '<strong>c</strong></span><strong>d</strong>[/u]e';
        $outcome = $this->_markup->render('[u]a[/b][hr]b[b]c[/u]d[/b][/u]e');
        $this->assertEquals($expected, $outcome);
    }

    public function testImgSrcsConstraints() {
        $this->assertEquals('F/\!ZLrFz',$this->_markup->render('F[img]/\!ZLrFz[/img]'));
    }

    public function testColorConstraintsAndJs() {
        $input = "<kokx> i think you mean? [color=\"onclick='foobar();'\"]your text[/color] DASPRiD";
        $expected = "&lt;kokx&gt; i think you mean? <span>your text</span> DASPRiD";
        $this->assertEquals($expected, $this->_markup->render($input));
    }

    public function testNeverEndingAttribute() {
        $input = "[color=\"green]your text[/color]";
        $expected = '<span>your text</span>';
        $this->assertEquals($expected, $this->_markup->render($input));
    }

    public function testTreatmentNonTags() {
        $input = '[span][acronym][h1][h2][h3][h4][h5][h6][nothing]'
               . '[/h6][/h5][/h4][/h3][/h2][/h1][/acronym][/span]';
        $expected = '<span><acronym><h1><h2><h3><h4><h5><h6>[nothing]'
                  . '</h6></h5></h4></h3></h2></h1></acronym></span>';
        $this->assertEquals($expected, $this->_markup->render($input));
    }

    public function testListItems() {
        $input = "[list][*]Foo*bar (item 1)\n[*]Item 2\n[*]Trimmed (Item 3)\n[/list]";
        $expected = "<ul><li>Foo*bar (item 1)</li><li>Item 2</li><li>Trimmed (Item 3)</li></ul>";
        $this->assertEquals($expected, $this->_markup->render($input));
    }

    public function testListTypes()
    {
        $types = array(
            '01'    => 'decimal-leading-zero',
            '1'     => 'decimal',
            'i'     => 'lower-roman',
            'I'     => 'upper-roman',
            'a'     => 'lower-alpha',
            'A'     => 'upper-alpha',
            'alpha' => 'lower-greek'
        );

        foreach ($types as $type => $style) {
            $input    = "[list={$type}][*]Foobar\n[*]Zend\n[/list]";
            $expected = "<ol style=\"list-style-type: {$style}\"><li>Foobar</li><li>Zend</li></ol>";
            $this->assertEquals($expected, $this->_markup->render($input));
        }
    }

    public function testHtmlTags() {
        $m = $this->_markup;

        $this->assertEquals('<strong>foo</strong>', $m->render('[b]foo[/b]'));
        $this->assertEquals('<span style="text-decoration: underline;">foo</span>',
                            $m->render('[u]foo[/u]'));
        $this->assertEquals('<em>foo</em>', $m->render('[i]foo[/i]'));
        $this->assertEquals('<cite>foo</cite>', $m->render('[cite]foo[/cite]'));
        $this->assertEquals('<del>foo</del>', $m->render('[del]foo[/del]'));
        $this->assertEquals('<ins>foo</ins>', $m->render('[ins]foo[/ins]'));
        $this->assertEquals('<sub>foo</sub>', $m->render('[sub]foo[/sub]'));
        $this->assertEquals('<span>foo</span>', $m->render('[span]foo[/span]'));
        $this->assertEquals('<acronym>foo</acronym>', $m->render('[acronym]foo[/acronym]'));
        $this->assertEquals('<h1>F</h1>', $m->render('[h1]F[/h1]'));
        $this->assertEquals('<h2>R</h2>', $m->render('[h2]R[/h2]'));
        $this->assertEquals('<h3>E</h3>', $m->render('[h3]E[/h3]'));
        $this->assertEquals('<h4>E</h4>', $m->render('[h4]E[/h4]'));
        $this->assertEquals('<h5>A</h5>', $m->render('[h5]A[/h5]'));
        $this->assertEquals('<h6>Q</h6>', $m->render('[h6]Q[/h6]'));
        $this->assertEquals('<span style="color: red;">foo</span>', $m->render('[color=red]foo[/color]'));
        $this->assertEquals('<span style="color: #00FF00;">foo</span>', $m->render('[color=#00FF00]foo[/color]'));

        $expected = '<code><span style="color: #000000">' . "\n"
                  . '<span style="color: #0000BB">&lt;?php<br /></span>'
                  . "<span style=\"color: #007700\">exit;</span>\n</span>\n</code>";

        $this->assertEquals($expected, $m->render("[code]<?php\nexit;[/code]"));
        $this->assertEquals('<p>I</p>', $m->render('[p]I[/p]'));
        $this->assertEquals('N',
                $m->render('[ignore]N[/ignore]'));
        $this->assertEquals('<blockquote>M</blockquote>', $m->render('[quote]M[/quote]'));

        $this->assertEquals('<hr />foo<hr />bar[/hr]', $m->render('[hr]foo[hr]bar[/hr]'));
    }

    public function testWrongNesting()
    {
        $this->assertEquals('<strong>foo<em>bar</em></strong>',
                                $this->_markup->render('[b]foo[i]bar[/b][/i]'));
        $this->assertEquals('<strong>foo<em>bar</em></strong><em>kokx</em>',
                                $this->_markup->render('[b]foo[i]bar[/b]kokx[/i]'));
    }

    public function testHtmlAliases() {
        $m = $this->_markup;

        $this->assertEquals($m->render('[b]F[/b]'), $m->render('[bold]F[/bold]'));
        $this->assertEquals($m->render('[bold]R[/bold]'), $m->render('[strong]R[/strong]'));
        $this->assertEquals($m->render('[i]E[/i]'), $m->render('[i]E[/i]'));
        $this->assertEquals($m->render('[i]E[/i]'), $m->render('[italic]E[/italic]'));
        $this->assertEquals($m->render('[i]A[/i]'), $m->render('[emphasized]A[/emphasized]'));
        $this->assertEquals($m->render('[i]Q[/i]'), $m->render('[em]Q[/em]'));
        $this->assertEquals($m->render('[u]I[/u]'), $m->render('[underline]I[/underline]'));
        $this->assertEquals($m->render('[cite]N[/cite]'), $m->render('[citation]N[/citation]'));
        $this->assertEquals($m->render('[del]G[/del]'), $m->render('[deleted]G[/deleted]'));
        $this->assertEquals($m->render('[ins]M[/ins]'), $m->render('[insert]M[/insert]'));
        $this->assertEquals($m->render('[s]E[/s]'),$m->render('[strike]E[/strike]'));
        $this->assertEquals($m->render('[sub]-[/sub]'), $m->render('[subscript]-[/subscript]'));
        $this->assertEquals($m->render('[sup]D[/sup]'), $m->render('[superscript]D[/superscript]'));
        $this->assertEquals($m->render('[url]google.com[/url]'), $m->render('[a]google.com[/a]'));
        $this->assertEquals($m->render('[img]http://google.com/favicon.ico[/img]'),
                            $m->render('[image]http://google.com/favicon.ico[/image]'));
    }

    public function testEmptyTagName()
    {
        $this->assertEquals('[]', $this->_markup->render('[]'));
    }

    public function testStyleAlignCombination()
    {
        $m = $this->_markup;
        $this->assertEquals('<h1 style="color: green;text-align: left;">Foobar</h1>',
                            $m->render('[h1 style="color: green" align=left]Foobar[/h1]'));
        $this->assertEquals('<h1 style="color: green;text-align: center;">Foobar</h1>',
                            $m->render('[h1 style="color: green;" align=center]Foobar[/h1]'));
    }

    public function testXssInAttributeValues()
    {
        $m = $this->_markup;
        $this->assertEquals('<strong class="&quot;&gt;xss">foobar</strong>',
                            $m->render('[b class=\'">xss\']foobar[/b]'));
    }

    public function testWrongNestedLists()
    {
        $m = $this->_markup;
        // thanks to PadraicB for finding this
        $input = <<<BBCODE
[list]
[*] Subject 1
[list]
[*] First
[*] Second
[/list]
[*] Subject 2
[/list]
BBCODE;
        $m->render($input);
    }

    public function testAttributeWithoutValue()
    {
        $m = $this->_markup;

        $this->assertEquals('<strong>foobar</strong>', $m->render('[b=]foobar[/b]'));
    }

    public function testRemoveTag()
    {
        $this->_markup->removeTag('b');

        $this->assertEquals('[b]bar[/b]', $this->_markup->render('[b]bar[/b]'));
    }

    public function testClearTags()
    {
        $this->_markup->clearTags();

        $this->assertEquals('[i]foo[/i]', $this->_markup->render('[i]foo[/i]'));
    }

    public function testAddFilters()
    {
        $m = $this->_markup;

        $m->addFilter(new Zend_Filter_StringToUpper());

        $this->assertEquals('<strong>HELLO</strong>', $m->render('[b]hello[/b]'));
    }

}

// Call Zend_Markup_BbcodeAndHtmlTest::main()
// if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Zend_Markup_BbcodeAndHtmlTest::main") {
    Zend_Markup_BbcodeAndHtmlTest::main();
}
