<?php
namespace YouTrack;
require_once("requirements.php");
require_once("testconnection.php");


/**
 * Unit test for the youtrack attachment class.
 *
 * @author Nepomuk Fraedrich <info@nepda.eu>
 */
class AttachmentsTest extends \PHPUnit_Framework_TestCase
{

    private $singleAttachmentFile = 'test/testdata/attachment.xml';

    public function testCanCreateSimpleAttachment()
    {
        try {
            $attachment = new Attachment();
        } catch (\Exception $e) {
            $this->fail();
        }
        $this->assertInstanceOf('\\YouTrack\\Attachment', $attachment);
    }

    public function testCanCreateAttachmentFromResponse()
    {
        $xml = simplexml_load_file($this->singleAttachmentFile);
        $xml = $xml->children();
        $xml = $xml[0];
        try {
            $attachment = new Attachment($xml);
        } catch (\Exception $e) {
            $this->fail();
        }
        $this->assertInstanceOf('\\YouTrack\\Attachment', $attachment);
    }

    private function createAttachment()
    {
        $xml = simplexml_load_file($this->singleAttachmentFile);
        $xml = $xml->children();
        $xml = $xml[0];
        return $attachment = new Attachment($xml);
    }

    public function testUrlIsSetAfterXmlLoad()
    {
        $attachment = $this->createAttachment();
        $this->assertEquals('http://example.com/file.abc', $attachment->getUrl());
    }

    public function testIdIsSetAfterXmlLoad()
    {
        $attachment = $this->createAttachment();
        $this->assertEquals('62-180', $attachment->getId());
    }

    public function testNameIsSetAfterXmlLoad()
    {
        $attachment = $this->createAttachment();
        $this->assertEquals('attachment.txt', $attachment->getName());
    }

    public function testAuthorLoginIsSetAfterXmlLoad()
    {
        $attachment = $this->createAttachment();
        $this->assertEquals('root', $attachment->getAuthorLogin());
    }

    public function testGroupIsSetAfterXmlLoad()
    {
        $attachment = $this->createAttachment();
        $this->assertEquals('All Users', $attachment->getGroup());
    }

    public function testCreatedIsSetAfterXmlLoadWithTimezoneAmerica()
    {
        date_default_timezone_set('America/New_York');
        $attachment = $this->createAttachment();
        //26.09.13 16:05:32
        $this->assertInstanceOf('\\DateTime', $attachment->getCreated());
        $this->assertEquals('2013-09-26 14:05:32', $attachment->getCreated()->format('Y-m-d H:i:s'));
    }

    public function testCreatedIsSetAfterXmlLoadWithTimezoneGermany()
    {
        date_default_timezone_set('Europe/Berlin');
        $attachment = $this->createAttachment();
        //26.09.13 16:05:32
        $this->assertInstanceOf('\\DateTime', $attachment->getCreated());
        $this->assertEquals('2013-09-26 14:05:32', $attachment->getCreated()->format('Y-m-d H:i:s'));
    }

    public function testCreateAttachmentFromAttachment()
    {
        $attachment = $this->createAttachment();

        $expectedResult = unserialize(file_get_contents('test/testdata/request-response-create-attachment-serialized.txt'));

        /**
         * @var \YouTrack\Connection $youtrack
         */
        $youtrack = $this->getMock('\\YouTrack\\TestConnection', array('request'));

        $youtrack->expects($this->once())
            ->method('request')
            ->will($this->returnValue($expectedResult));

        $result = $youtrack->createAttachmentFromAttachment('TEST-2', $attachment);

        $this->assertEquals($expectedResult, $result);
    }

    public function createAttachmentProvider()
    {
        $createdDateTime = new \DateTime();

        return array(
            // name, authorLogin, created, group, expectedParams
            array('', '', null, '', array()),
            array('file.txt', '', null, '', array('name' => 'file.txt')),
            array('', 'authorX', null, '', array('authorLogin' => 'authorX')),
            array('', '', $createdDateTime, '', array('created' => $createdDateTime->getTimestamp() * 1000)),
            array('', '', null, 'groupX', array('group' => 'groupX'))
        );
    }

    /**
     * @dataProvider createAttachmentProvider
     */
    public function testCreateAttachment($name, $authorLogin, $created, $group, $params)
    {
        $attachmentFilename = __DIR__ . '/testdata/attachment.xml';
        $issueId = 'TEST-123';
        $expectedUrl = '/issue/' . rawurlencode($issueId) . '/attachment?' . http_build_query($params);
        $expectedResult = 'myResponseValue';

        $youtrack = $this->getMock('\\YouTrack\\TestConnection', array('request'));

        $youtrack->expects($this->once())
            ->method('request')
            ->with('POST', $expectedUrl, $attachmentFilename)
            ->will($this->returnValue($expectedResult));

        /** @var \YouTrack\Connection $youtrack */
        $result = $youtrack->createAttachment($issueId, $attachmentFilename, $name, $authorLogin, $created, $group);

        $this->assertEquals($expectedResult, $result);
    }

    public function testCreateAttachmentThrowsExceptionOnNonExistingFile()
    {
        $youtrack = $this->getMock('\\YouTrack\\TestConnection', array('request'));

        $this->setExpectedException('\Exception');

        /** @var \YouTrack\Connection $youtrack */
        $youtrack->createAttachment('TEST-123', '/non/existing/file.txt');
    }
}
