<?php
require_once dirname(__FILE__) . '/TestAbstract.php';
require_once 'Zend/Controller/Front.php';
require_once 'Zend/Controller/Request/Http.php';
require_once 'Zend/View/Helper/Navigation/Sitemap.php';

/**
 * Tests Zend_View_Helper_Navigation_Sitemap
 *
 */
class Zend_View_Helper_Navigation_SitemapTest
    extends Zend_View_Helper_Navigation_TestAbstract
{
    protected $_front;
    protected $_oldRequest;
    protected $_oldRouter;
    protected $_oldServer = array();

    /**
     * Class name for view helper to test
     *
     * @var string
     */
    protected $_helperName = 'Zend_View_Helper_Navigation_Sitemap';

    /**
     * View helper
     *
     * @var Zend_View_Helper_Navigation_Sitemap
     */
    protected $_helper;

    protected function setUp()
    {
        date_default_timezone_set('Europe/Berlin');

        if (isset($_SERVER['SERVER_NAME'])) {
            $this->_oldServer['SERVER_NAME'] = $_SERVER['SERVER_NAME'];
        }

        if (isset($_SERVER['SERVER_PORT'])) {
            $this->_oldServer['SERVER_PORT'] = $_SERVER['SERVER_PORT'];
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $this->_oldServer['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
        }

        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['REQUEST_URI'] = '/';

        $this->_front = Zend_Controller_Front::getInstance();
        $this->_oldRequest = $this->_front->getRequest();
        $this->_oldRouter = $this->_front->getRouter();

        $this->_front->resetInstance();
        $this->_front->setRequest(new Zend_Controller_Request_Http());
        $this->_front->getRouter()->addDefaultRoutes();

        parent::setUp();

        $this->_helper->setFormatOutput(true);
    }

    protected function tearDown()
    {
        if (null !== $this->_oldRequest) {
            $this->_front->setRequest($this->_oldRequest);
        } else {
            $this->_front->setRequest(new Zend_Controller_Request_Http());
        }
        $this->_front->setRouter($this->_oldRouter);

        foreach ($this->_oldServer as $key => $value) {
            $_SERVER[$key] = $value;
        }
    }

    public function testNullingOutNavigation()
    {
        $this->_helper->setContainer();
        $this->assertEquals(0, count($this->_helper->getContainer()));
    }

    public function testAutoloadContainerFromRegistry()
    {
        $oldReg = null;
        if (Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            $oldReg = Zend_Registry::get(self::REGISTRY_KEY);
        }
        Zend_Registry::set(self::REGISTRY_KEY, $this->_nav1);

        $this->_helper->setContainer(null);

        $expected = $this->_getExpected('sitemap/default1.xml');
        $actual = $this->_helper->render();

        Zend_Registry::set(self::REGISTRY_KEY, $oldReg);

        $this->assertEquals($expected, $expected);
    }

    public function testRenderSuppliedContainerWithoutInterfering()
    {
        $rendered1 = $this->_getExpected('sitemap/default1.xml');
        $rendered2 = $this->_getExpected('sitemap/default2.xml');

        $expected = array(
            'registered'       => $rendered1,
            'supplied'         => $rendered2,
            'registered_again' => $rendered1
        );
        $actual = array(
            'registered'       => $this->_helper->render(),
            'supplied'         => $this->_helper->render($this->_nav2),
            'registered_again' => $this->_helper->render()
        );

        $this->assertEquals($expected, $actual);
    }

    public function testUseAclRoles()
    {
        $acl = $this->_getAcl();
        $this->_helper->setAcl($acl['acl']);
        $this->_helper->setRole($acl['role']);

        $expected = $this->_getExpected('sitemap/acl.xml');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testUseAclButNoRole()
    {
        $acl = $this->_getAcl();
        $this->_helper->setAcl($acl['acl']);
        $this->_helper->setRole(null);

        $expected = $this->_getExpected('sitemap/acl2.xml');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testSettingMaxDepth()
    {
        $this->_helper->setMaxDepth(0);

        $expected = $this->_getExpected('sitemap/depth1.xml');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testSettingMinDepth()
    {
        $this->_helper->setMinDepth(1);

        $expected = $this->_getExpected('sitemap/depth2.xml');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testSettingBothDepths()
    {
        $this->_helper->setMinDepth(1)->setMaxDepth(2);

        $expected = $this->_getExpected('sitemap/depth3.xml');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testDropXmlDeclaration()
    {
        $old = $this->_helper->getUseXmlDeclaration();
        $this->_helper->setUseXmlDeclaration(false);

        $expected = $this->_getExpected('sitemap/nodecl.xml');
        $this->assertEquals($expected, $this->_helper->render($this->_nav2));

        $this->_helper->setUseXmlDeclaration($old);
    }

    public function testThrowExceptionOnInvalidLoc()
    {
        $nav = clone $this->_nav2;
        $nav->addPage(array('label' => 'Invalid', 'uri' => 'http://w.'));

        try {
            $this->_helper->render($nav);
        } catch (Zend_View_Exception $e) {
            $expected = sprintf(
                    'Encountered an invalid URL for Sitemap XML: "%s"',
                    'http://w.');
            $actual = $e->getMessage();
            $this->assertEquals($expected, $actual);
            return;
        }

        $this->fail('A Zend_View_Exception was not thrown on invalid <loc />');
    }

    public function testDisablingValidators()
    {
        $nav = clone $this->_nav2;
        $nav->addPage(array('label' => 'Invalid', 'uri' => 'http://w.'));
        $this->_helper->setUseSitemapValidators(false);

        $expected = $this->_getExpected('sitemap/invalid.xml');
        $this->assertEquals($expected, $this->_helper->render($nav));
    }

    public function testUseSchemaValidation()
    {
        $this->markTestSkipped('Skipped because it fetches XSD from web');
        return;
        $nav = clone $this->_nav2;
        $this->_helper->setUseSitemapValidators(false);
        $this->_helper->setUseSchemaValidation(true);
        $nav->addPage(array('label' => 'Invalid', 'uri' => 'http://w.'));

        try {
            $this->_helper->render($nav);
        } catch (Zend_View_Exception $e) {
            $expected = sprintf(
                    'Sitemap is invalid according to XML Schema at "%s"',
                    Zend_View_Helper_Navigation_Sitemap::SITEMAP_XSD);
            $actual = $e->getMessage();
            $this->assertEquals($expected, $actual);
            return;
        }

        $this->fail('A Zend_View_Exception was not thrown when using Schema validation');
    }
}
