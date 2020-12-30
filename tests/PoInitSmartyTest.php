<?php
namespace Geekwright\Po;

class PoInitSmartyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PoInitSmarty
     */
    protected $object;

    /**
     * @var PoFile
     */
    protected $poFile;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp():void
    {
        if (!class_exists('\Smarty')) {
            $this->markTestSkipped('Smarty is not available.');
        }
        $smarty = new \Smarty;
        $smarty->addPluginsDir(__DIR__ . '/smarty/plugins');
        $this->poFile = new PoFile();
        $this->object = new PoInitSmarty($smarty, $this->poFile);
    }

    public function testGetSetPoFile()
    {
        $pofile = new PoFile();
        $this->object->setPoFile($pofile);
        $actual = $this->object->getPoFile();
        $this->assertSame($pofile, $actual);
    }

    public function testAddGetSetMsgctxtArgNames()
    {
        $value = array();
        $this->object->setMsgctxtArgNames($value);
        $actual = $this->object->getMsgctxtArgNames();
        $this->assertEquals($value, $actual);

        $tag1 = 'tag1';
        $tag2 = 'tag2';
        $value = array($tag1, $tag2);
        $this->object->addMsgctxtArgNames($tag1);
        $this->object->addMsgctxtArgNames($tag2);
        $actual = $this->object->getMsgctxtArgNames();
        $this->assertEquals($value, $actual);
    }

    public function testAddGetSetMsgidArgNames()
    {
        $value = array();
        $this->object->setMsgidArgNames($value);
        $actual = $this->object->getMsgidArgNames();
        $this->assertEquals($value, $actual);

        $tag1 = 'tag1';
        $tag2 = 'tag2';
        $value = array($tag1, $tag2);
        $this->object->addMsgidArgNames($tag1);
        $this->object->addMsgidArgNames($tag2);
        $actual = $this->object->getMsgidArgNames();
        $this->assertEquals($value, $actual);
    }

    public function testAddGetSetMsgidPluralArgNames()
    {
        $value = array();
        $this->object->setMsgidPluralArgNames($value);
        $actual = $this->object->getMsgidPluralArgNames();
        $this->assertEquals($value, $actual);

        $tag1 = 'tag1';
        $tag2 = 'tag2';
        $value = array($tag1, $tag2);
        $this->object->addMsgidPluralArgNames($tag1);
        $this->object->addMsgidPluralArgNames($tag2);
        $actual = $this->object->getMsgidPluralArgNames();
        $this->assertEquals($value, $actual);
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
        $this->object->msginitFile(__DIR__ . '/smarty/templates/index.tpl');
        $entries = $this->poFile->getEntries();
        $this->assertArrayHasKey('This is how the story goes.', $entries);
        $this->assertArrayHasKey('%d pig went to the market|%d pigs went to the market', $entries);
        $entry = $entries['%d pig went to the market|%d pigs went to the market'];
        $this->assertEquals('%d pigs went to the market', $entry->get(PoTokens::PLURAL));
        $this->assertTrue($entry->hasFlag('php-format'));
    }

    public function testMsginitFile_error()
    {
        $this->expectException('\Geekwright\Po\Exceptions\FileNotReadableException');
        $this->object->msginitFile(__DIR__ . '/smarty/templates/no-such-file.tpl');
    }

    public function testMsginitString_gettext()
    {
        $template = '{_ msgid="My String"}';

        $this->object->msginitString($template, basename(__FILE__));
        $entries = $this->poFile->getEntries();
        /** @var \Geekwright\Po\PoEntry $entry */
        $entry = $entries['My String'];
        $this->assertEquals('My String', $entry->get(PoTokens::MESSAGE));
    }

    public function testMsginitString_ngettext()
    {
        $template = '{_ msgid="My String" msgid_plural="My Strings" num=2}';

        $this->object->msginitString($template, basename(__FILE__));
        $entries = $this->poFile->getEntries();
        /** @var \Geekwright\Po\PoEntry $entry */
        $entry = reset($entries);
        $this->assertEquals('My String', $entry->get(PoTokens::MESSAGE));
        $this->assertEquals('My Strings', $entry->get(PoTokens::PLURAL));
    }

    public function testMsginitString_pgettext()
    {
        $template = '{_ msgid="My String" msgctxt="pgettextTest" num=2}';

        $this->object->msginitString($template, basename(__FILE__));
        $entries = $this->poFile->getEntries();
        /** @var \Geekwright\Po\PoEntry $entry */
        $entry = reset($entries);
        $this->assertEquals('My String', $entry->get(PoTokens::MESSAGE));
        $this->assertEquals('pgettextTest', $entry->get(PoTokens::CONTEXT));
    }

    public function testMsginitString_error()
    {
        $template = '{_ msgid="}';
        $this->expectException('\SmartyCompilerException');
        $this->object->msginitString($template, basename(__FILE__));
    }

    public function testMsginitString_noPoFile()
    {
        $smarty = new \Smarty;
        $smarty->addPluginsDir(__DIR__ . '/smarty/plugins');
        $object = new PoInitSmarty($smarty);
        $template = '{_ msgid="My String"}';

        $object->msginitString($template, basename(__FILE__));
        $entries = $object->getPoFile()->getEntries();
        /** @var \Geekwright\Po\PoEntry $entry */
        $entry = $entries['My String'];
        $this->assertEquals('My String', $entry->get(PoTokens::MESSAGE));
    }
}
