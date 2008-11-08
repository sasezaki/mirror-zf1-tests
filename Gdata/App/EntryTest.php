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
 * @package      Zend_Gdata_App
 * @subpackage   UnitTests
 * @copyright    Copyright (c) 2006-2008 Zend Technologies USA Inc. (http://www.zend.com);
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Gdata/App/Entry.php';
require_once 'Zend/Gdata/App.php';
require_once 'Zend/Gdata/TestUtility/MockHttpClient.php';

/**
 * @package Zend_Gdata_App
 * @subpackage UnitTests
 */
class Zend_Gdata_App_EntryTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->enryText = file_get_contents(
                'Zend/Gdata/App/_files/EntrySample1.xml',
                true);
        $this->httpEntrySample = file_get_contents(
                'Zend/Gdata/App/_files/EntrySampleHttp1.txt',
                true);
        $this->enry = new Zend_Gdata_App_Entry();
        
        $this->adapter = new Test_Zend_Gdata_MockHttpClient();
        $this->client = new Zend_Gdata_HttpClient();
        $this->client->setAdapter($this->adapter);
        $this->service = new Zend_Gdata_App($this->client);
    }
      
    public function testEmptyEntryShouldHaveEmptyExtensionsList()
    {
        $this->assertTrue(is_array($this->enry->extensionElements));
        $this->assertTrue(count($this->enry->extensionElements) == 0);
    }
      
    public function testEmptyEntryToAndFromStringShouldMatch()
    {
        $enryXml = $this->enry->saveXML();
        $newEntry = new Zend_Gdata_App_Entry();
        $newEntry->transferFromXML($enryXml);
        $newEntryXml = $newEntry->saveXML();
        $this->assertTrue($enryXml == $newEntryXml);
    }
    
    public function testConvertEntryToAndFromString()
    {
        $this->enry->transferFromXML($this->enryText);
        $enryXml = $this->enry->saveXML();
        $newEntry = new Zend_Gdata_App_Entry();
        $newEntry->transferFromXML($enryXml);
/*
        $this->assertEquals(1, count($newEntry->entry));
        $this->assertEquals('dive into mark', $newEntry->title->text);
        $this->assertEquals('text', $newEntry->title->type);
        $this->assertEquals('2005-07-31T12:29:29Z', $newEntry->updated->text);
        $this->assertEquals('tag:example.org,2003:3', $newEntry->id->text);
        $this->assertEquals(2, count($newEntry->link));
        $this->assertEquals('http://example.org/', 
                $newEntry->getAlternateLink()->href); 
        $this->assertEquals('en', 
                $newEntry->getAlternateLink()->hrefLang); 
        $this->assertEquals('text/html', 
                $newEntry->getAlternateLink()->type); 
        $this->assertEquals('http://example.org/enry.atom', 
                $newEntry->getSelfLink()->href); 
        $this->assertEquals('application/atom+xml', 
                $newEntry->getSelfLink()->type); 
        $this->assertEquals('Copyright (c) 2003, Mark Pilgrim', 
                $newEntry->rights->text); 
        $entry = $newEntry->entry[0];
        $this->assertEquals('Atom draft-07 snapshot', $entry->title->text);
        $this->assertEquals('tag:example.org,2003:3.2397', 
                $entry->id->text);
        $this->assertEquals('2005-07-31T12:29:29Z', $entry->updated->text);
        $this->assertEquals('2003-12-13T08:29:29-04:00', 
                $entry->published->text);
        $this->assertEquals('Mark Pilgrim', 
                $entry->author[0]->name->text);
        $this->assertEquals('http://example.org/', 
                $entry->author[0]->uri->text);
        $this->assertEquals(2, count($entry->contributor)); 
        $this->assertEquals('Sam Ruby', 
                $entry->contributor[0]->name->text); 
        $this->assertEquals('Joe Gregorio', 
                $entry->contributor[1]->name->text); 
        $this->assertEquals('xhtml', $entry->content->type);
*/
    }
    
    public function testCanSetAndGetEtag()
    {
        $data = "W/&amp;FooBarBaz&amp;";
        $this->enry->setEtag($data);
        $this->assertEquals($this->enry->getEtag(), $data);
    }
    
    public function testCanSetAndgetService()
    {
        $data = new Zend_Gdata_App();
        $this->enry->setService($data);
        $this->assertEquals($this->enry->getService(), $data);
        
        $data = null;
        $this->enry->setService($data);
        $this->assertEquals($this->enry->getService(), $data);
    }
    
    public function testsetServiceProvidesFluentInterface()
    {
        $result = $this->enry->setService(null);
        $this->assertEquals($this->enry, $result);
    }
    
    public function testGetHttpClientPullsFromServiceInstance()
    {
        $s = new Zend_Gdata_App();
        $this->enry->setService($s);
        
        $c = new Zend_Gdata_HttpClient();
        $s->setHttpClient($c);
        $this->assertEquals($this->enry->getHttpClient(),
                $s->getHttpClient());
        
        $c = new Zend_Http_Client();
        $s->setHttpClient($c);
        $this->assertEquals($this->enry->getHttpClient(),
                $s->getHttpClient($c));
    }
    
    public function testSetHttpClientPushesIntoServiceInstance()
    {
        $s = new Zend_Gdata_App();
        $this->enry->setService($s);
        
        $c = new Zend_Gdata_HttpClient();
        $this->enry->setHttpClient($c);
        $this->assertEquals(get_class($s->getHttpClient()),
                'Zend_Gdata_HttpClient');
        
        $c = new Zend_Http_Client();
        $this->enry->setHttpClient($c);
        $this->assertEquals(get_class($s->getHttpClient()),
                'Zend_Http_Client');
    }
    
    public function testSaveSupportsGdataV2()
    {
        // Prepare mock response
        $this->adapter->setResponse("HTTP/1.1 201 Created");
        
        // Make sure that we're using protocol v2
        $this->service->setMajorProtocolVersion(2);
        $this->enry->setService($this->service);
        
        // Set a URL for posting, so that save() will work
        $editLink = new Zend_Gdata_App_extension_Link('http://example.com',
                'edit');
        $this->enry->setLink(array($editLink));
        
        // Perform a (mock) save
        $this->enry->save();
        
        // Check to make sure that a v2 header was sent
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'GData-Version: 2')
                $found = true;
        }
        $this->assertTrue($found,
                'GData-Version header missing or incorrect.');
    }
    
    public function testDeleteSupportsGdataV2()
    {
        // Prepare mock response
        $this->adapter->setResponse("HTTP/1.1 200 OK");
        
        // Make sure that we're using protocol v2
        $this->service->setMajorProtocolVersion(2);
        $this->enry->setService($this->service);
        
        // Set a URL for posting, so that save() will work
        $editLink = new Zend_Gdata_App_extension_Link('http://example.com',
                'edit');
        $this->enry->setLink(array($editLink));
        
        // Perform a (mock) save
        $this->enry->delete();
        
        // Check to make sure that a v2 header was sent
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'GData-Version: 2')
                $found = true;
        }
        $this->assertTrue($found,
                'GData-Version header missing or incorrect.');
    }
    
    public function testIfMatchHeaderCanBeSetOnSave()
    {
        $etagOverride = 'foo';
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->newEntry();
        $entry->link = array(new Zend_Gdata_App_Extension_Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $newEntry = $entry->save(null, null,
                array('If-Match' => $etagOverride));
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-Match: ' . $etagOverride)
                $found = true;
        }
        $this->assertTrue($found,
                'If-Match header not found or incorrect');
    }
    
    public function testIfNoneMatchHeaderCanBeSetOnSave()
    {
        $etagOverride = 'foo';
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->newEntry();
        $entry->link = array(new Zend_Gdata_App_Extension_Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $newEntry = $entry->save(null, null,
                array('If-None-Match' => $etagOverride));
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-None-Match: ' . $etagOverride)
                $found = true;
        }
        $this->assertTrue($found,
                'If-None-Match header not found or incorrect');
    }
    
    public function testCanSetUriOnSave()
    {
        $uri = 'http://example.net/foo/bar';
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->newEntry();
        $newEntry = $entry->save($uri);
        $request = $this->adapter->popRequest();
        $uriObject = Zend_Uri_Http::fromString($uri);
        $uriObject->setPort('80');
        $this->assertEquals($uriObject, $request->uri);
    }
    
    public function testCanSetClassnameOnSave()
    {
        $className = 'Zend_Gdata_Entry';
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->newEntry();
        $entry->link = array(new Zend_Gdata_App_Extension_Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $newEntry = $entry->save(null, $className);
        $this->assertEquals($className, get_class($newEntry));
    }
    
    public function testIfNoneMatchSetOnReload()
    {
        $etag = 'ABCD1234';
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->newEntry();
        $entry->link = array(new Zend_Gdata_App_Extension_Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $newEntry = $entry->reload();
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-None-Match: ' . $etag)
                $found = true;
        }
        $this->assertTrue($found,
                'If-None-Match header not found or incorrect');
    }
    
    public function testIfNoneMatchCanBeSetOnReload()
    {
        $etagOverride = 'foo';
        $etag = 'ABCD1234';
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->newEntry();
        $entry->link = array(new Zend_Gdata_App_Extension_Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $newEntry = $entry->reload(null, null, 
                array('If-None-Match' => $etagOverride));
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-None-Match: ' . $etagOverride)
                $found = true;
        }
        $this->assertTrue($found,
                'If-None-Match header not found or incorrect');
    }
    
    public function testReloadReturnsEntryObject()
    {
        $etag = 'ABCD1234';
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->newEntry();
        $entry->link = array(new Zend_Gdata_App_Extension_Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $newEntry = $entry->reload();
        $this->assertEquals('Zend_Gdata_App_Entry', get_class($newEntry));
    }
    
    public function testReloadPopulatesEntryObject()
    {
        $etag = 'ABCD1234';
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->newEntry();
        $entry->link = array(new Zend_Gdata_App_Extension_Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $newEntry = $entry->reload();
        $this->assertEquals('Hello world', $newEntry->title->text);
    }
    
    public function testReloadDoesntThrowExceptionIfNoEtag()
    {
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->newEntry();
        $entry->link = array(new Zend_Gdata_App_Extension_Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $newEntry = $entry->reload();
        $this->assertEquals('Zend_Gdata_App_Entry', get_class($newEntry));
    }
    
    public function testReloadExtractsURIFromEditLink()
    {
        $expectedUri = 'http://www.example.com';
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->newEntry();
        $entry->link = array(new Zend_Gdata_App_Extension_Link(
                $expectedUri,
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $newEntry = $entry->reload();
        $requestUri = $this->adapter->popRequest()->uri;
        $expectedUriObject = Zend_Uri_Http::fromString($expectedUri);
        $expectedUriObject->setPort('80');
        $this->assertEquals($expectedUriObject, $requestUri);
    }
    
    public function testReloadAllowsCustomURI()
    {
        $uriOverride = 'http://www.example.org';
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->newEntry();
        $entry->link = array(new Zend_Gdata_App_Extension_Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $newEntry = $entry->reload($uriOverride);
        $requestUri = $this->adapter->popRequest()->uri;
        $uriOverrideObject = Zend_Uri_Http::fromString($uriOverride);
        $uriOverrideObject->setPort('80');
        $this->assertEquals($uriOverrideObject, $requestUri);
    }
    
    public function testReloadReturnsNullIfEntryNotModified()
    {
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $this->adapter->setResponse('HTTP/1.1 304 Not Modified');
        $entry = $this->service->newEntry();
        $entry->link = array(new Zend_Gdata_App_Extension_Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $newEntry = $entry->reload();
        $this->assertEquals(null, $newEntry);
    }
    
    public function testCanSetReloadReturnClassname()
    {
        $className = 'Zend_Gdata_Entry';
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->newEntry();
        $entry->link = array(new Zend_Gdata_App_Extension_Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $newEntry = $entry->reload(null, $className);
        $this->assertEquals($className, get_class($newEntry));
    }
    
    public function testReloadInheritsClassname()
    {
        $className = 'Zend_Gdata_Entry';
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = new $className;
        $entry->setService($this->service);
        $entry->link = array(new Zend_Gdata_App_Extension_Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $newEntry = $entry->reload();
        $this->assertEquals($className, get_class($newEntry));
    }
}
