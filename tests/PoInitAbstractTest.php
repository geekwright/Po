<?php
namespace Geekwright\Po;

class PoInitAbstractTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PoInitAbstract
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        //$this->object = new PoInitAbstract;
        $this->object = $this->getMockForAbstractClass('Geekwright\Po\PoInitAbstract');
        $this->object->expects($this->any())
             ->method('msginitString')
             ->will($this->returnValue(new PoFile));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testGetSetPoFile()
    {
        $pofile = new PoFile();
        $this->object->setPoFile($pofile);
        $actual = $this->object->getPoFile();
        $this->assertSame($pofile, $actual);
    }

    public function testAddGetSetGettextTags()
    {
        $value = array();
        $this->object->setGettextTags($value);
        $actual = $this->object->getGettextTags();
        $this->assertEquals($value, $actual);

        $tag1 = 'tag1';
        $tag2 = 'tag2';
        $value = array($tag1, $tag2);
        $this->object->addGettextTags($tag1);
        $this->object->addGettextTags($tag2);
        $actual = $this->object->getGettextTags();
        $this->assertEquals($value, $actual);
    }

    public function testAddGetSetNgettextTags()
    {
        $value = array();
        $this->object->setNgettextTags($value);
        $actual = $this->object->getNgettextTags();
        $this->assertEquals($value, $actual);

        $tag1 = 'tag1';
        $tag2 = 'tag2';
        $value = array($tag1, $tag2);
        $this->object->addNgettextTags($tag1);
        $this->object->addNgettextTags($tag2);
        $actual = $this->object->getNgettextTags();
        $this->assertEquals($value, $actual);
    }

    public function testAddGetSetPgettextTags()
    {
        $value = array();
        $this->object->setPgettextTags($value);
        $actual = $this->object->getPgettextTags();
        $this->assertEquals($value, $actual);

        $tag1 = 'tag1';
        $tag2 = 'tag2';
        $value = array($tag1, $tag2);
        $this->object->addPgettextTags($tag1);
        $this->object->addPgettextTags($tag2);
        $actual = $this->object->getPgettextTags();
        $this->assertEquals($value, $actual);
    }

    public function testMsginitFile()
    {
        $result = $this->object->msginitFile(__FILE__);
        $this->assertInstanceOf('Geekwright\Po\PoFile', $result);
    }

    public function testMsginitFileException()
    {
        $this->expectException('Geekwright\Po\Exceptions\FileNotReadableException');
        $result = $this->object->msginitFile(__DIR__ . '/notavalidfile');
    }

    public function testEscapeForPo()
    {
        $actual = $this->object->escapeForPo("'test\r\n'");
        $this->assertEquals('test\n', $actual);
    }

    public function testCheckPhpFormatFlag()
    {
        $entry = new PoEntry;
        $entry->set(PoTokens::MESSAGE, 'This %%s should not trigger flag.');
        $this->object->checkPhpFormatFlag($entry);
        $this->assertFalse($entry->hasFlag('php-format'));

        $entry = new PoEntry;
        $entry->set(PoTokens::MESSAGE, 'This %s should trigger flag.');
        $this->object->checkPhpFormatFlag($entry);
        $this->assertTrue($entry->hasFlag('php-format'));

        $entry = new PoEntry;
        $entry->set(PoTokens::PLURAL, 'This %2$d should trigger flag.');
        $this->object->checkPhpFormatFlag($entry);
        $this->assertTrue($entry->hasFlag('php-format'));

        $entry = new PoEntry;
        $entry->set(PoTokens::PLURAL, 'This %\'.9d should trigger flag.');
        $this->object->checkPhpFormatFlag($entry);
        $this->assertTrue($entry->hasFlag('php-format'));

        $entry = new PoEntry;
        $entry->set(PoTokens::MESSAGE, 'This %V should not trigger flag.');
        $this->object->checkPhpFormatFlag($entry);
        $this->assertFalse($entry->hasFlag('php-format'));
    }
}
