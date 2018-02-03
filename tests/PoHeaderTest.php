<?php
namespace Geekwright\Po;

class PoHeaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PoHeader
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PoHeader;
        $this->object->buildDefaultHeader();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function test__construct()
    {
        $poheader = new PoHeader;
        $actual = $poheader->get(PoTokens::MESSAGE);
        $this->assertSame("", $actual);
    }

    public function testGetSetHeader()
    {
        $value = 'nplurals=1; plural=0;';
        $this->object->setHeader('plural-forms', $value);
        $actual = $this->object->getHeader('Plural-Forms');
        $this->assertEquals($value, $actual);
    }

    public function testSetCreateDate()
    {
        $time = time();
        $expected = gmdate('Y-m-d H:iO', $time);
        $this->object->setCreateDate($time);
        $actual = $this->object->getHeader('POT-Creation-Date');
        $this->assertEquals($expected, $actual);
    }

    public function testSetRevisionDate()
    {
        $time = time();
        $expected = gmdate('Y-m-d H:iO', $time);
        $this->object->setRevisionDate($time);
        $actual = $this->object->getHeader('PO-Revision-Date');
        $this->assertEquals($expected, $actual);
    }

    public function testBuildDefaultHeader()
    {
        $header = new PoHeader;
        $header->buildDefaultHeader();
        $actual = $header->getHeader('Content-Type');
        $expected = 'text/plain; charset=UTF-8';
        $this->assertEquals($expected, $actual);
    }

    public function testDumpEntry()
    {
        $output = $this->object->dumpEntry();
        $actual = substr($output, -2);
        $expected = "\n\n";
        $this->assertEquals($expected, $actual);
        $actual = substr($output, 0, 1);
        $expected = "#";
        $this->assertEquals($expected, $actual);
        $this->assertTrue(false !== strpos($output, 'Content-Type'));
        $this->assertTrue(false !== strpos($output, 'PO-Revision-Date'));
    }

    public function testStoreStructuredHeaderDumpEntry()
    {
        $header = new PoHeader;
        $reflection = new \ReflectionClass(get_class($header));
        $method = $reflection->getMethod('storeStructuredHeader');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($header));
    }
}
