<?php
namespace Geekwright\Po;

class PoFileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PoFile
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PoFile;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testCreateKey()
    {
        $msgid = 'message';
        $msgctxt = 'context';
        $msgid_plural = 'plural';

        $key = PoFile::createKey($msgid);
        $expected = $msgid;
        $this->assertEquals($key, $expected);

        $key = PoFile::createKey($msgid, $msgctxt);
        $expected = $msgctxt . '|' . $msgid;
        $this->assertEquals($key, $expected);

        $key = PoFile::createKey($msgid, null, $msgid_plural);
        $expected = $msgid . '|' . $msgid_plural;
        $this->assertEquals($key, $expected);

        $key = PoFile::createKey($msgid, $msgctxt, $msgid_plural);
        $expected = $msgctxt . '|' . $msgid . '|' . $msgid_plural;
        $this->assertEquals($key, $expected);

        $key = $this->object->createKey($msgid, $msgctxt, $msgid_plural);
        $expected = PoFile::createKey($msgid, $msgctxt, $msgid_plural);
        $this->assertEquals($key, $expected);
    }

    public function testCreateKeyFromEntry()
    {

        $msgid = 'message';
        $msgctxt = 'context';
        $msgid_plural = 'plural';

        $entry = new PoEntry();

        $entry->set(PoTokens::MESSAGE, $msgid);
        $key = $this->object->createKeyFromEntry($entry);
        $expected = PoFile::createKey($msgid);
        $this->assertEquals($key, $expected);

        $entry->set(PoTokens::CONTEXT, $msgctxt);
        $key = $this->object->createKeyFromEntry($entry);
        $expected = PoFile::createKey($msgid, $msgctxt);
        $this->assertEquals($key, $expected);

        $entry->set(PoTokens::PLURAL, $msgid_plural);
        $key = $this->object->createKeyFromEntry($entry);
        $expected = PoFile::createKey($msgid, $msgctxt, $msgid_plural);
        $this->assertEquals($key, $expected);
    }

    public function testGetSetHeaderEntry()
    {
        $header = new PoHeader;
        $this->object->setHeaderEntry($header);
        $actual = $this->object->getHeaderEntry();
        $this->assertSame($header, $actual);
    }

    public function testGetAddEntries()
    {
        $pofile = new PoFile;
        $actual = $pofile->getEntries();
        $expected = array();
        $this->assertEquals($expected, $actual);

        $entry1 = new PoEntry;
        $entry1->set(PoTokens::MESSAGE, 'Hello.');
        $entry1->set(PoTokens::TRANSLATED, 'Bonjour!');
        $entry1->set(PoTokens::TRANSLATOR_COMMENTS, 'Just saying hello');

        $entry2 = new PoEntry;
        $entry2->set(PoTokens::MESSAGE, 'Hello.');
        $entry2->set(PoTokens::TRANSLATED, 'Bonjour!');
        $entry2->set(PoTokens::TRANSLATOR_COMMENTS, 'Just saying hello');

        $pofile->addEntry($entry1);
        $pofile->addEntry($entry2, false);
        $entries = $pofile->getEntries();
        $actual = reset($entries);
        $this->assertSame($entry1, $actual);

        $pofile->addEntry($entry2, true);
        $entries = $pofile->getEntries();
        $actual = reset($entries);
        $this->assertSame($entry2, $actual);
        $this->assertEquals(1, count($entries));

        $entry1->set(PoTokens::MESSAGE, 'Goodbye.');
        $entry1->set(PoTokens::TRANSLATED, 'Au Revoir.');
        $pofile->addEntry($entry1);
        $entries = $pofile->getEntries();
        $this->assertEquals(2, count($entries));
    }

    public function testGetSetDumpUnkeyedEntries()
    {
        $pofile = new PoFile;

        $entry1 = new PoEntry;
        $entry1->add(PoTokens::OBSOLETE, 'msgid "Hello."');
        $entry1->add(PoTokens::OBSOLETE, 'msgstr "Bonjour!"');

        $pofile->addEntry($entry1);
        $expected = array($entry1);
        $actual = $pofile->getUnkeyedEntries();
        $this->assertSame($expected, $actual);

        $expected = array();
        $pofile->setUnkeyedEntries($expected);
        $actual = $pofile->getUnkeyedEntries();
        $this->assertSame($expected, $actual);

        $expected = array($entry1);
        $pofile->setUnkeyedEntries($expected);
        $actual = $pofile->getUnkeyedEntries();
        $this->assertSame($expected, $actual);

        $output = $pofile->dumpString();
        $pofile = new PoFile;
        $pofile->parsePoSource($output);
        $entries = $pofile->getUnkeyedEntries();
        $entry = reset($entries);
        $actual = $entry->dumpEntry();
        $expected = "#~ msgid \"Hello.\"\n#~ msgstr \"Bonjour!\"\n\n";
        $this->assertEquals($expected, $actual);
    }

    public function testMergeEntry()
    {
        $pofile = new PoFile;

        $entry1 = new PoEntry;
        $entry1->set(PoTokens::MESSAGE, 'Hello.');
        $entry1->set(PoTokens::TRANSLATED, 'Bonjour!');

        $entry2 = new PoEntry;
        $entry2->set(PoTokens::MESSAGE, 'Hello.');
        $entry2->set(PoTokens::TRANSLATED, 'Bonjour!');
        $entry2->set(PoTokens::TRANSLATOR_COMMENTS, 'Just saying hello');
        $entry2->set(PoTokens::EXTRACTED_COMMENTS, 'Just saying hello');

        $this->assertTrue($pofile->mergeEntry($entry1));
        $this->assertTrue($pofile->mergeEntry($entry2));
        $entries = $pofile->getEntries();
        $actual = reset($entries);
        $this->assertSame($entry1, $actual);
        $this->assertSame($entry1->get(PoTokens::EXTRACTED_COMMENTS), array('Just saying hello'));
        $this->assertNull($entry1->get(PoTokens::TRANSLATOR_COMMENTS));

        $this->assertEquals(1, count($entries));

        $entry3 = new PoEntry;
        $entry3->add(PoTokens::OBSOLETE, 'msgid "Hello."');
        $entry3->add(PoTokens::OBSOLETE, 'msgstr "Bonjour!"');
        $this->assertFalse($pofile->mergeEntry($entry3));
    }

    public function testRemoveEntry()
    {
        $entry1 = new PoEntry;
        $entry1->set(PoTokens::MESSAGE, 'Hello.');

        $entry2 = new PoEntry;
        $entry2->set(PoTokens::MESSAGE, 'Hello');

        $pofile = new PoFile;
        $pofile->addEntry($entry1);
        $pofile->addEntry($entry2);

        $entries = $pofile->getEntries();
        $this->assertEquals(2, count($entries));

        $entry2->set(PoTokens::MESSAGE, 'Hello.');
        $this->assertTrue($pofile->removeEntry($entry2));

        $entries = $pofile->getEntries();
        $this->assertEquals(1, count($entries));
        $actual = reset($entries);
        $this->assertSame($entry1, $actual);

        $pofile = new PoFile;
        $pofile->addEntry($entry1);
        $entries = $pofile->getEntries();
        $this->assertEquals(1, count($entries));
        $actual = reset($entries);
        $this->assertSame($entry1, $actual);
        $this->assertTrue($pofile->removeEntry($entry1));
        $entries = $pofile->getEntries();
        $this->assertEquals(0, count($entries));
        $this->assertFalse($pofile->removeEntry($entry1));

        $entry3 = new PoEntry;
        $entry3->add(PoTokens::OBSOLETE, 'msgid "Hello."');
        $entry3->add(PoTokens::OBSOLETE, 'msgstr "Bonjour!"');

        $this->assertTrue($pofile->addEntry($entry3));
        $entries = $pofile->getUnkeyedEntries();
        $this->assertEquals(1, count($entries));
        $this->assertTrue($pofile->removeEntry($entry3));
        $entries = $pofile->getUnkeyedEntries();
        $this->assertEquals(0, count($entries));
        $this->assertFalse($pofile->removeEntry($entry3));
    }

    public function testReadPoFile()
    {
        $pofile = new PoFile;
        $pofile->readPoFile(__DIR__ . '/files/fr.po');
        $entries = $pofile->getEntries();
        $this->assertEquals(2, count($entries));

        $header = $pofile->getHeaderEntry();
        $actual = $header->getHeader('plural-forms');
        $expected = 'nplurals=2; plural=(n > 1);';
        $this->assertEquals($expected, $actual);

        $entry = $pofile->findEntry('This is how the story goes.');
        $this->assertInstanceOf('Geekwright\Po\PoEntry', $entry);
        $actual = $entry->getAsString(PoTokens::TRANSLATED);
        $expected = "C'est la narration de l'histoire.";
        $this->assertEquals($expected, $actual);

        $entry = $pofile->findEntry("%d pig went to the market", null, "%d pigs went to the market");
        $this->assertInstanceOf('Geekwright\Po\PoEntry', $entry);
        $actual = $entry->getAsStringArray(PoTokens::TRANSLATED);
        $expected = "%d cochons se sont rendus au marché.";
        $this->assertEquals($expected, $actual[1]);
    }

    public function testNotReadablePoFile()
    {
        $pofile = new PoFile;
        $this->expectException('Geekwright\Po\Exceptions\FileNotReadableException');
        $pofile->readPoFile(__DIR__ . '/files/nosuchfile.po');
    }

    public function testInvalidPoFile()
    {
        $pofile = new PoFile;
        $this->expectException('Geekwright\Po\Exceptions\UnrecognizedInputException');
        $pofile->readPoFile(__DIR__ . '/files/nota.po');
    }

    public function testDumpString()
    {
        $pofile = new PoFile;
        $entry1 = new PoEntry;
        $entry1->set(PoTokens::OBSOLETE, 'msgstr "Bonjour!"');
        $this->assertTrue($pofile->addEntry($entry1));

        $output1 = $pofile->dumpString();
        $expected = "msgstr \"Bonjour!\"\n\n\n";
        $actual = substr($output1, -20);
        $this->assertSame($expected, $actual);
    }

    public function testDumpParseString()
    {
        $pofile = new PoFile;
        $pofile->readPoFile(__DIR__ . '/files/fr.po');
        // verify that dump to parse is lossless
        $output1 = $pofile->dumpString();
        $pofile = new PoFile;
        $pofile->parsePoSource($output1);
        $output2 = $pofile->dumpString();
        $this->assertEquals($output1, $output2);
    }

    public function testWritePoFile()
    {
        $pofile = new PoFile;
        $filename = tempnam('/tmp', 'PO');
        $pofile->readPoFile(__DIR__ . '/files/fr.po');
        $output1 = $pofile->dumpString();
        $pofile->writePoFile($filename);
        $pofile = new PoFile;
        $pofile->readPoFile($filename);
        $output2 = $pofile->dumpString();
        $this->assertEquals($output1, $output2);
        $entries = $pofile->getEntries();
        $this->assertEquals(2, count($entries));
    }

    public function testWritePoFileException()
    {
        $pofile = new PoFile;
        $filename = tempnam('/tmp', 'PO');
        $this->assertTrue(touch($filename), 'Failed to create temp file');
        $this->assertTrue(chmod($filename, 0444), 'Failed to set temp file R/O');
        $pofile->readPoFile(__DIR__ . '/files/fr.po');
        $this->expectException('Geekwright\Po\Exceptions\FileNotWritableException');
        $pofile->writePoFile($filename);
    }
}
