<?php
namespace Geekwright\Po;

class PoEntryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PoEntry
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp():void
    {
        $this->object = new PoEntry;
    }

    public function testAddGetSet()
    {
        $entry = new PoEntry;
        $actual = $entry->get(PoTokens::TRANSLATOR_COMMENTS);
        $this->assertNull($actual);

        $value = 'this is a comment';
        $entry->set(PoTokens::TRANSLATOR_COMMENTS, $value);
        $actual = $entry->get(PoTokens::TRANSLATOR_COMMENTS);
        $this->assertEquals($value, $actual);

        $value2 = 'another comment';
        $entry->add(PoTokens::TRANSLATOR_COMMENTS, $value2);
        $expected = array($value, $value2);
        $actual = $entry->get(PoTokens::TRANSLATOR_COMMENTS);
        $this->assertEquals($expected, $actual);

        $entry->add(PoTokens::REFERENCE, 'ref');
        $actual = $entry->get(PoTokens::REFERENCE);
        $expected = array('ref');
        $this->assertEquals($expected, $actual);
    }

    public function testAddQuoted()
    {
        $this->object->set(PoTokens::MESSAGE, null);
        $this->object->addQuoted(PoTokens::MESSAGE, '""');
        $this->object->addQuoted(PoTokens::MESSAGE, '"First\n"');
        $this->object->addQuoted(PoTokens::MESSAGE, '"Second\n"');
        $actual = $this->object->getAsString(PoTokens::MESSAGE);
        $this->assertEquals("First\nSecond\n", $actual);
    }

    public function testAddQuotedAtPosition()
    {
        $this->object->set(PoTokens::TRANSLATED, null);
        $this->object->addQuotedAtPosition(PoTokens::TRANSLATED, 1, '""');
        $this->object->addQuotedAtPosition(PoTokens::TRANSLATED, 1, '"First\n"');
        $this->object->addQuotedAtPosition(PoTokens::TRANSLATED, 1, '"Second\n"');
        $actual = $this->object->getAsStringArray(PoTokens::TRANSLATED);
        $this->assertEquals(array(1=>"First\nSecond\n"), $actual);

        $this->object->set(PoTokens::TRANSLATED, null);
        $this->object->set(PoTokens::TRANSLATED, array("First\n"));
        $this->object->addQuotedAtPosition(PoTokens::TRANSLATED, 0, '"Second\n"');
        $actual = $this->object->getAsStringArray(PoTokens::TRANSLATED);
        $this->assertEquals(array(0=>"First\nSecond\n"), $actual);
    }

    public function testDumpEntry()
    {
        $entry = new PoEntry;
        $entry->set(PoTokens::MESSAGE, 'Hello.');
        $entry->set(PoTokens::TRANSLATED, 'Bonjour!');
        $entry->set(PoTokens::TRANSLATOR_COMMENTS, 'Just saying');
        $entry->add(PoTokens::TRANSLATOR_COMMENTS, 'hello');
        $entry->set(PoTokens::REFERENCE, 'ref');

        $actual = $entry->dumpEntry();

        $expected = "# Just saying\n# hello\n#: ref\nmsgid \"Hello.\"\nmsgstr \"Bonjour!\"\n\n";
        $this->assertEquals($expected, $actual);

        $entry = new PoEntry;
        $entry->set(PoTokens::MESSAGE, '');
        $entry->add(PoTokens::MESSAGE, 'Hello.');
        $entry->set(PoTokens::TRANSLATED, '');
        $entry->add(PoTokens::TRANSLATED, 'Bonjour!');
        $entry->add(PoTokens::CONTEXT, 'context');

        $actual = $entry->dumpEntry();

        $expected = "msgctxt \"context\"\nmsgid \"\"\n\"Hello.\"\nmsgstr \"\"\n\"Bonjour!\"\n\n";
        $this->assertEquals($expected, $actual);

        $entry = new PoEntry;
        $entry->add(PoTokens::MESSAGE, 'One');
        $entry->add(PoTokens::PLURAL, 'Several');
        $entry->addQuotedAtPosition(PoTokens::TRANSLATED, 0, 'Onewa');
        $entry->addQuotedAtPosition(PoTokens::TRANSLATED, 1, 'Everalsa');

        $actual = $entry->dumpEntry();

        $expected = "msgid \"One\"\nmsgid_plural \"Several\"\nmsgstr[0] \"Onewa\"\nmsgstr[1] \"Everalsa\"\n\n";
        $this->assertEquals($expected, $actual);
    }

    public function testHasFlag()
    {
        $this->object->set(PoTokens::FLAG, null);
        $this->assertFalse($this->object->hasFlag('fuzzy'));
        $this->object->set(PoTokens::FLAG, 'fuzzy');

        $this->assertTrue($this->object->hasFlag('fuzzy'));
        $this->assertFalse($this->object->hasFlag('futzy'));

        $this->object->set(PoTokens::FLAG, null);
        $this->assertFalse($this->object->hasFlag('fuzzy'));
        $this->object->addFlag('fuzzy');
        $this->assertTrue($this->object->hasFlag('fuzzy'));
        $this->assertFalse($this->object->hasFlag('futzy'));
        $this->object->addFlag('futzy');
        $this->assertTrue($this->object->hasFlag('futzy'));

        $this->object->set(PoTokens::FLAG, null);
        $this->object->set(PoTokens::FLAG, ' php-format , fuzzy, OdD-StUfF');
        $this->assertTrue($this->object->hasFlag('php-format'));
        $this->assertTrue($this->object->hasFlag('fuzzy'));
        $this->assertTrue($this->object->hasFlag('odd-stuff'));
        $this->assertFalse($this->object->hasFlag('futzy'));
        $this->object->add(PoTokens::FLAG, 'separate');
        $this->assertTrue($this->object->hasFlag('separate'));
        $this->object->addFlag('futzy');
        $this->assertTrue($this->object->hasFlag('fuzzy'));
        $this->assertTrue($this->object->hasFlag('futzy'));
        $this->assertTrue($this->object->hasFlag('separate'));
    }
}
