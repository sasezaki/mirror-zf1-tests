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
 * @package    Zend_Oauth
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once 'Zend/Oauth.php';

class Test_Http_Client_19485876 extends Zend_Http_Client {}

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Oauth
 */
class Zend_OauthTest extends PHPUnit_Framework_TestCase
{

    public function teardown()
    {
        Zend_Oauth::clearHttpClient();
    }

    public function testCanSetCustomHttpClient()
    {
        Zend_Oauth::setHttpClient(new Test_Http_Client_19485876());
        $this->assertType('Test_Http_Client_19485876', Zend_Oauth::getHttpClient());
    }

    public function testGetHttpClientResetsParameters()
    {
        $client = new Test_Http_Client_19485876();
        $client->setParameterGet(array('key'=>'value'));
        Zend_Oauth::setHttpClient($client);
        $resetClient = Zend_Oauth::getHttpClient();
        $resetClient->setUri('http://www.example.com');
        $this->assertEquals('http://www.example.com:80', $resetClient->getUri(true));
    }

    public function testGetHttpClientResetsAuthorizationHeader()
    {
        $client = new Test_Http_Client_19485876();
        $client->setHeaders('Authorization', 'realm="http://www.example.com",oauth_version="1.0"');
        Zend_Oauth::setHttpClient($client);
        $resetClient = Zend_Oauth::getHttpClient();
        $this->assertEquals(null, $resetClient->getHeader('Authorization'));
    }

    /**
     * @group ZF-10182
     * @dataProvider providerOauthClientOauthOptions
     */
    public function testOauthClientOauthOptionsInConstructor($oauthOptions)
    {
        require_once 'Zend/Oauth/Client.php';
        $client = new Zend_Oauth_Client($oauthOptions);
        $this->assertEquals('GET', $client->getRequestMethod());
        $this->assertEquals('http://www.example.com', $client->getSiteUrl());
    }

    /**
     * @group ZF-10182
     * @dataProvider providerOauthClientConfigHttpClient
     */
    public function testOauthClientConfigHttpClientInConstructor($configHttpClient, $expected)
    {
        require_once 'Zend/Oauth/Client.php';
        $client = new Zend_Oauth_Client(null, null, $configHttpClient);
        $config = $client->getAdapter()->getConfig();
        $this->assertEquals($expected['rfc'], $config['rfc3986_strict']);
        $this->assertEquals($expected['useragent'], $config['useragent']);
        $this->assertEquals($expected['timeout'], $config['timeout']);
    }

    public function providerOauthClientOauthOptions()
    {
        $options = array(
            'requestMethod' => 'GET',
            'siteUrl'       => 'http://www.example.com'
        );

        require_once 'Zend/Config.php';
        return array(
            array($options),
            array(new Zend_Config($options))
        );
    }

    public function providerOauthClientConfigHttpClient()
    {
        return array(
            array(
                array('adapter' => 'Zend_Http_Client_Adapter_Test'),
                array('rfc' => true,
                      'timeout' => 10,
                      'useragent' => 'Zend_Http_Client'
                )
            ),
            array(
                new Zend_Config(array('adapter' => 'Zend_Http_Client_Adapter_Test')),
                array('rfc' => true,
                      'timeout' => 10,
                      'useragent' => 'Zend_Http_Client'
                )
            ),
            array(
                new Zend_Config(array(
                   'adapter' => 'Zend_Http_Client_Adapter_Test',
                   'rfc3986_strict' => false,
                   'timeout'        => 100,
                   'useragent' => 'Zend_Http_ClientCustom'
                )),
                array('rfc' => false,
                      'timeout' => 100,
                      'useragent' => 'Zend_Http_ClientCustom'
                )
            ),
            array(
                null,
                array('rfc'       => true,
                      'timeout'   => 10,
                      'useragent' => 'Zend_Http_Client'
                )
            ),
        );
    }

    /**
     * @group ZF-10851
     */
    public function testOauthClientAcceptsRealmConfigurationOption()
    {
        $options = array(
            'realm'			=> 'http://www.example.com'
        );

        require_once 'Zend/Oauth/Client.php';
        $client = new Zend_Oauth_Client($options);
        $this->assertEquals('http://www.example.com', $client->getRealm());
    }

    /**
     * @group ZF-10851
     */
    public function testOauthClientPreparationWithRealmConfigurationOption()
    {
        require_once "Zend/Oauth/Token/Access.php";

        $options = array(
            'requestMethod' => 'GET',
            'siteUrl'       => 'http://www.example.com',
            'realm'			=> 'someRealm'
        );
        $token = new Zend_Oauth_Token_Access();

        require_once 'Zend/Oauth/Client.php';
        $client = new Zend_Oauth_Client($options);
        $this->assertEquals(NULL,$client->getHeader('Authorization'));

        $client->setToken($token);
        $client->setUri('http://oauth.example.com');
        $client->prepareOauth();

        $this->assertNotContains('realm=""',$client->getHeader('Authorization'));
        $this->assertContains('realm="someRealm"',$client->getHeader('Authorization'));
    }
}
