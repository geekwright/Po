<?php
namespace Geekwright\Po;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-02-17 at 18:30:38.
 */
class PoTokensTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PoTokens
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        //$this->object = new PoTokens;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testPoTokens()
    {
        $this->assertNotNull(PoTokens::TRANSLATOR_COMMENTS);
        $this->assertNotNull(PoTokens::EXTRACTED_COMMENTS);
        $this->assertNotNull(PoTokens::REFERENCE);
        $this->assertNotNull(PoTokens::FLAG);
        $this->assertNotNull(PoTokens::PREVIOUS);
        $this->assertNotNull(PoTokens::OBSOLETE);
        $this->assertNotNull(PoTokens::CONTEXT);
        $this->assertNotNull(PoTokens::MESSAGE);
        $this->assertNotNull(PoTokens::PLURAL);
        $this->assertNotNull(PoTokens::TRANSLATED);
        $this->assertNotNull(PoTokens::TRANSLATED_PLURAL);
        $this->assertNotNull(PoTokens::CONTINUED_DATA);

        $this->assertEquals(PoTokens::TRANSLATOR_COMMENTS, '#');
        $this->assertEquals(PoTokens::EXTRACTED_COMMENTS, '#.');
        $this->assertEquals(PoTokens::REFERENCE, '#:');
        $this->assertEquals(PoTokens::FLAG, '#,');
        $this->assertEquals(PoTokens::PREVIOUS, '#|');
        $this->assertEquals(PoTokens::OBSOLETE, '#~');
        $this->assertEquals(PoTokens::CONTEXT, 'msgctxt');
        $this->assertEquals(PoTokens::MESSAGE, 'msgid');
        $this->assertEquals(PoTokens::PLURAL, 'msgid_plural');
        $this->assertEquals(PoTokens::TRANSLATED, 'msgstr');
        $this->assertEquals(PoTokens::TRANSLATED_PLURAL, 'msgstr[');
        $this->assertEquals(PoTokens::CONTINUED_DATA, '"');
    }
}
