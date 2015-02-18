<?php

namespace Geekwright\Po;

/**
 * PoInitInterface provides 'msginit' like interface which can take a source file,
 * recognize gettext like function tokens, and capture the translatable strings
 * in a PoFile object.
 *
 * @category  Po
 * @package   Po\PoInitInterface
 * @author    Richard Griffith <richard@geekwright.com>
 * @copyright 2015 Richard Griffith
 * @license   GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @link      https://github.com/geekwright/Po
 */
interface PoInitInterface
{
    /**
     * setPoFile
     * @param PoFile $poFile set the PoFile object to be used in msginit
     *
     * @return void
     */
    public function setPoFile(PoFile $poFile);

    /**
     * setGettextTags - set tags used for gettext like functions
     * @param string[] $tags array of tags to set
     *
     * @return void
     */
    public function setGettextTags($tags);

    /**
     * addGettextTags - add tags used for gettext like functions
     * @param string|string[] $tags tag, or array of tags to add
     *
     * @return void
     */
    public function addGettextTags($tags);

    /**
     * setNgettextTags - set tags used for ngettext like functions
     * @param string[] $tags array of tags to set
     *
     * @return void
     */
    public function setNgettextTags($tags);

    /**
     * addNgettextTags - add tags used for ngettext like functions
     * @param string|string[] $tags tag, or array of tags to add
     *
     * @return void
     */
    public function addNgettextTags($tags);

    /**
     * setPgettextTags - set tags used for pgettext like functions
     * @param string[] $tags array of tags to set
     *
     * @return void
     */
    public function setPgettextTags($tags);

    /**
     * addPgettextTags - add tags used for pgettext like functions
     * @param string|string[] $tags tag, or array of tags to add
     *
     * @return void
     */
    public function addPgettextTags($tags);


    /**
     * msginitFile - inspect the supplied source file, capture gettext references
     * in our PoFile object
     *
     * @param string $filename name of php file
     * @return PoFile
     * @throws FileNotReadableException
     */
    public function msginitFile($filename);

    /**
     * msginitString - inspect the supplied source, capture gettext references
     * in our PoFile object.
     *
     * @param string $source  php source code
     * @param string $refname source identification used for PO reference comments
     * @return PoFile
     */
    public function msginitString($source, $refname);

}
