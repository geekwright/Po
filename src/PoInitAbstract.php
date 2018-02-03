<?php

namespace Geekwright\Po;

use Geekwright\Po\Exceptions\FileNotReadableException;

/**
 * PoInitAbstract provides a structure for 'msginit' like logic which can take
 * a source PHP file, recognize gettext like function tokens, and capture the
 * translatable strings in a PoFile object.
 *
 * @category  Extractors
 * @package   Po
 * @author    Richard Griffith <richard@geekwright.com>
 * @copyright 2015-2018 Richard Griffith
 * @license   GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @link      https://github.com/geekwright/Po
 */
abstract class PoInitAbstract
{
    /**
     * @var PoFile $poFile object to be used in msginit
     */
    protected $poFile = null;

    /**
     * @var string[] $gettextTags tags for gettext constructs, i.e. tag($msgid)
     */
    protected $gettextTags = array('gettext', 'gettext_noop', '_');

    /**
     * @var string[] $pgettextTags tags for pgettext constructs, i.e. tag($msgctxt, $msgid)
     */
    protected $pgettextTags = array('pgettext');

    /**
     * @var string[] $ngettextTags tags for ngettext constructs, i.e. tag($msgid, $msgid_plural)
     */
    protected $ngettextTags = array('ngettext');

    /**
     * Get the PoFile object used in msginit process
     *
     * @return PoFile
     */
    public function getPoFile(): PoFile
    {
        return $this->poFile;
    }

    /**
     * Set the PoFile object to use in msginit process
     *
     * @param PoFile $poFile a PoFile object
     *
     * @return void
     */
    public function setPoFile(PoFile $poFile): void
    {
        $this->poFile = $poFile;
    }

    /**
     * Get tags used for gettext like functions
     *
     * @return string[]
     */
    public function getGettextTags(): array
    {
        return $this->gettextTags;
    }

    /**
     * Set tags used for gettext like functions
     *
     * @param string[] $tags array of tags to set
     *
     * @return void
     */
    public function setGettextTags(array $tags): void
    {
        $this->gettextTags = $tags;
    }

    /**
     * Add tags used for gettext like functions
     *
     * @param string|string[] $tags tag, or array of tags to add
     *
     * @return void
     */
    public function addGettextTags($tags): void
    {
        $this->gettextTags = array_merge($this->gettextTags, (array) $tags);
    }

    /**
     * Get tags used for ngettext like functions
     *
     * @return string[]
     */
    public function getNgettextTags(): array
    {
        return $this->ngettextTags;
    }

    /**
     * setNgettextTags - set tags used for ngettext like functions
     * @param string[] $tags array of tags to set
     *
     * @return void
     */
    public function setNgettextTags(array $tags): void
    {
        $this->ngettextTags = $tags;
    }

    /**
     * Add tags used for ngettext like functions
     *
     * @param string|string[] $tags tag, or array of tags to add
     *
     * @return void
     */
    public function addNgettextTags($tags): void
    {
        $this->ngettextTags = array_merge($this->ngettextTags, (array) $tags);
    }

    /**
     * Get tags used for pgettext like functions
     *
     * @return string[]
     */
    public function getPgettextTags(): array
    {
        return $this->pgettextTags;
    }

    /**
     * Set tags used for pgettext like functions
     *
     * @param string[] $tags array of tags to set
     *
     * @return void
     */
    public function setPgettextTags(array $tags): void
    {
        $this->pgettextTags = $tags;
    }

    /**
     * Add tags used for pgettext like functions
     *
     * @param string|string[] $tags tag, or array of tags to add
     *
     * @return void
     */
    public function addPgettextTags($tags): void
    {
        $this->pgettextTags = array_merge($this->pgettextTags, (array) $tags);
    }

    /**
     * Inspect the supplied source file, capture gettext references as a PoFile object
     *
     * @param string $filename name of source file
     *
     * @return PoFile
     *
     * @throws FileNotReadableException
     */
    public function msginitFile(string $filename): PoFile
    {
        if (!is_readable($filename)) {
            $source = false;
        } else {
            $source = file_get_contents($filename);
        }
        if (false===$source) {
            throw new FileNotReadableException($filename);
        }
        return $this->msginitString($source, $filename);
    }

    /**
     * Inspect the supplied source, capture gettext references as a PoFile object
     *
     * @param string $source  php source code
     * @param string $refname source identification used for PO reference comments
     *
     * @return PoFile
     */
    abstract public function msginitString(string $source, string $refname): PoFile;

    /**
     * Prepare a string from tokenized output for use in a po file. Remove any
     * surrounding quotes, escape control characters and double quotes.
     *
     * @param string $string raw string (T_STRING) identified by php token_get_all
     *
     * @return string
     */
    public function escapeForPo(string $string): string
    {
        if ($string[0]=='"' || $string[0]=="'") {
            $string = substr($string, 1, -1);
        }
        $string = str_replace("\r\n", "\n", $string);
        $string = stripcslashes($string);
        return addcslashes($string, "\0..\37\"");
    }

    /**
     * Check the supplied entry for sprintf directives and set php-format flag if found
     *
     * @param PoEntry $entry entry to check
     *
     * @return void
     */
    public function checkPhpFormatFlag(PoEntry $entry): void
    {
        if (preg_match(
            '#(?<!%)%(?:\d+\$)?[+-]?(?:[ 0]|\'.{1})?-?\d*(?:\.\d+)?[bcdeEufFgGosxX]#',
            $entry->get(PoTokens::MESSAGE) . $entry->get(PoTokens::PLURAL)
        )) {
            $entry->addFlag('php-format');
        }
    }
}
