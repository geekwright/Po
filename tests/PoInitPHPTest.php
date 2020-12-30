<?php
namespace Geekwright\Po;

class PoInitPHPTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PoInitPHP
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp():void
    {
        $this->object = new PoInitPHP;
    }

    public function testGetSetPoFile()
    {
        $pofile = new PoFile();
        $this->object->setPoFile($pofile);
        $actual = $this->object->getPoFile();
        $this->assertSame($pofile, $actual);

        $init = new PoInitPHP($pofile);
        $actual = $init->getPoFile();
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

    public function testMsginit()
    {
        $poinit = new PoInitPHP;
        $pofile = $poinit->msginitFile(__DIR__ . '/files/inittest.php');
        $this->assertInstanceOf('Geekwright\Po\PoFile', $pofile);
        $entries = $pofile->getEntries();
        $this->assertEquals(10, count($entries));
        $entry = $pofile->findEntry('File', 'menu');
        $this->assertInstanceOf('Geekwright\Po\PoEntry', $entry);
        $actual = $entry->getAsString(PoTokens:: EXTRACTED_COMMENTS);
        $this->assertEquals('menu entry', $actual);
        $entry = $pofile->findEntry("%d pig went to the market.", null, "%d pigs went to the market.");
        $this->assertInstanceOf('Geekwright\Po\PoEntry', $entry);
        $refs = $entry->get(PoTokens:: REFERENCE);
        $this->assertTrue(is_array($refs));
        $this->assertEquals(2, count($refs));
        $this->assertTrue($entry->hasFlag('php-format'));
        $entry = $pofile->findEntry('Control Panel\nwith a lot of options\n');
        $this->assertInstanceOf('Geekwright\Po\PoEntry', $entry);
    }
}
