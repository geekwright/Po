<?php

namespace Geekwright\Po;

use Geekwright\Po\Exceptions\FileNotReadableException;

/**
 * PoInitPHP provides 'msginit' like logic which can take a source PHP file,
 * recognize gettext like function tokens, and capture the translatable strings
 * in a PoFile object.
 *
 * @category  Po
 * @package   Po\PoInitPHP
 * @author    Richard Griffith <richard@geekwright.com>
 * @copyright 2015 Richard Griffith
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
     * getPoFile - set the PoFile object to used in msginit
     *
     * @return PoFile
     */
    public function getPoFile()
    {
        return $this->poFile;
    }

    /**
     * setPoFile
     * @param PoFile $poFile set the PoFile object to be used in msginit
     *
     * @return void
     */
    public function setPoFile(PoFile $poFile)
    {
        $this->poFile = $poFile;
    }

    /**
     * getGettextTags - get tags used for gettext like functions
     *
     * @return string[]
     */
    public function getGettextTags()
    {
        return $this->gettextTags;
    }

    /**
     * setGettextTags - set tags used for gettext like functions
     * @param string[] $tags array of tags to set
     *
     * @return void
     */
    public function setGettextTags($tags)
    {
        $this->gettextTags = $tags;
    }

    /**
     * addGettextTags - add tags used for gettext like functions
     * @param string|string[] $tags tag, or array of tags to add
     *
     * @return void
     */
    public function addGettextTags($tags)
    {
        $this->gettextTags = array_merge($this->gettextTags, (array) $tags);
    }

    /**
     * getNgettextTags - get tags used for ngettext like functions
     *
     * @return string[]
     */
    public function getNgettextTags()
    {
        return $this->ngettextTags;
    }

    /**
     * setNgettextTags - set tags used for ngettext like functions
     * @param string[] $tags array of tags to set
     *
     * @return void
     */
    public function setNgettextTags($tags)
    {
        $this->ngettextTags = $tags;
    }

    /**
     * addNgettextTags - add tags used for ngettext like functions
     * @param string|string[] $tags tag, or array of tags to add
     *
     * @return void
     */
    public function addNgettextTags($tags)
    {
        $this->ngettextTags = array_merge($this->ngettextTags, (array) $tags);
    }

    /**
     * getPgettextTags - get tags used for pgettext like functions
     *
     * @return string[]
     */
    public function getPgettextTags()
    {
        return $this->pgettextTags;
    }

    /**
     * setPgettextTags - set tags used for pgettext like functions
     * @param string[] $tags array of tags to set
     *
     * @return void
     */
    public function setPgettextTags($tags)
    {
        $this->pgettextTags = $tags;
    }

    /**
     * addPgettextTags - add tags used for pgettext like functions
     * @param string|string[] $tags tag, or array of tags to add
     *
     * @return void
     */
    public function addPgettextTags($tags)
    {
        $this->pgettextTags = array_merge($this->pgettextTags, (array) $tags);
    }

    /**
     * msginitFile - inspect the supplied source file, capture gettext references
     * as a PoFile object
     *
     * @param string $filename name of source file
     * @return PoFile
     * @throws FileNotReadableException
     */
    public function msginitFile($filename)
    {
        $source = file_get_contents($filename);
        if (false===$source) {
            throw new FileNotReadableException($filename);
        }
        return $this->msginitString($source, $filename);
    }

    /**
     * msginitString - inspect the supplied source, capture gettext references
     * as a PoFile object.
     *
     * @param string $source  php source code
     * @param string $refname source identification used for PO reference comments
     * @return PoFile
     */
    abstract public function msginitString($source, $refname);

    /**
     * escapeForPo prepare a string from tokenized output for use in a po file.
     * Remove any surrounding quotes, escape control characters and double qoutes
     * @param string $string raw string (T_STRING) identified by php token_get_all
     * @return string
     */
    protected function escapeForPo($string)
    {
        if ($string[0]=='"' || $string[0]=="'") {
            $string = substr($string, 1, -1);
        }
        $string = str_replace("\r\n", "\n", $string);
        $string = stripcslashes($string);
        return addcslashes($string, "\0..\37\"");
    }
}
