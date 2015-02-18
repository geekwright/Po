<?php

namespace Geekwright\Po;

use Geekwright\Po\Exceptions\FileNotReadableException;

/**
 * PoInitPHP provides 'msginit' like logic which can take a Smarty template file,
 * recognize gettext like function tokens, and capture the translatable strings
 * in a PoFile object.
 *
 * The Smarty functions are expected to be in the format:
 *   funcname(msgid="message" msgid_plural="plural message" msgctxt="context")
 * The specifics of the function ('funcname') and the argument names ('msgid',
 * 'msgid_plural', and 'msgctxt') can be specified.
 *
 * Smarty3 is required.
 *
 * @category  Po
 * @package   Po\PoInitSmarty
 * @author    Richard Griffith <richard@geekwright.com>
 * @copyright 2015 Richard Griffith
 * @license   GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @link      https://github.com/geekwright/Po
 */
class PoInitSmarty implements PoInitInterface
{
    /**
     * @var PoFile $poFile object to be used in msginit
     */
    protected $poFile = null;

    /**
     * @var string[] $gettextTags tags for gettext constructs, i.e. tag($msgid)
     */
    protected $gettextTags = array('gettext', '_');

    /**
     * @var string[] $pgettextTags tags for pgettext constructs, i.e. tag($msgctxt, $msgid)
     */
    protected $pgettextTags = array();

    /**
     * @var string[] $ngettextTags tags for ngettext constructs, i.e. tag($msgid, $msgid_plural)
     */
    protected $ngettextTags = array();

    /**
     * @var string[] $msgidArgNames names of Smarty function argments for msgid
     */
    protected $msgidArgNames = array('msgid');

    /**
     * @var string[] $msgidPluralArgNames names of Smarty function argments for msgid_plural
     */
    protected $msgidPluralArgNames = array('msgid_plural');

    /**
     * @var string[] $msgctxtArgNames names of Smarty function argments for msgctxt
     */
    protected $msgctxtArgNames = array('msgctxt');

    /**
     * __construct
     * @param \Smarty     $smarty a Smarty 3 instance
     * @param PoFile|null $poFile a PoFile object to be used in msginit
     */
    public function __construct(\Smarty $smarty, PoFile $poFile = null)
    {
        $this->smarty = $smarty;
        $this->poFile = $poFile;
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
     * setMsgctxtArgNames - set argument name(s) used for the msgctxt
     * @param string[] $argNames array of argument names to set
     *
     * @return void
     */
    public function setMsgctxtArgNames($argNames)
    {
        $this->msgctxtArgNames = $argNames;
    }

    /**
     * addMsgctxtArgNames - add argument name(s) used for the msgctxt
     * @param string|string[] $argNames argument name(s) to add
     *
     * @return void
     */
    public function addMsgctxtArgNames($argNames)
    {
        $tags = is_scalar($argNames) ? array($argNames) : $argNames;
        $this->msgctxtArgNames = array_merge($this->msgctxtArgNames, $argNames);
    }

    /**
     * setMsgidArgNames - set argument name(s) used for the msgid
     * @param string[] $argNames array of argument names to set
     *
     * @return void
     */
    public function setMsgidArgNames($argNames)
    {
        $this->msgidArgNames = $argNames;
    }

    /**
     * addMsgidArgNames - add argument name(s) used for the msgid
     * @param string|string[] $argNames argument name(s) to add
     *
     * @return void
     */
    public function addMsgidArgNames($argNames)
    {
        $tags = is_scalar($argNames) ? array($argNames) : $argNames;
        $this->msgidArgNames = array_merge($this->msgidArgNames, $argNames);
    }

    /**
     * setMsgidPluralArgNames - set argument name(s) used for the msgid_plural
     * @param string[] $argNames array of argument names to set
     *
     * @return void
     */
    public function setMsgidPluralArgNames($argNames)
    {
        $this->msgidPluralArgNames = $argNames;
    }

    /**
     * addMsgidPluralArgNames - add argument name(s) used for the msgid_plural
     * @param string|string[] $argNames argument name(s) to add
     *
     * @return void
     */
    public function addMsgidPluralArgNames($argNames)
    {
        $tags = is_scalar($argNames) ? array($argNames) : $argNames;
        $this->msgidPluralArgNames = array_merge($this->msgidPluralArgNames, $argNames);
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
        $tags = is_scalar($tags) ? array($tags) : $tags;
        $this->gettextTags = array_merge($this->gettextTags, $tags);
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
        $tags = is_scalar($tags) ? array($tags) : $tags;
        $this->ngettextTags = array_merge($this->ngettextTags, $tags);
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
        $tags = is_scalar($tags) ? array($tags) : $tags;
        $this->pgettextTags = array_merge($this->pgettextTags, $tags);
    }


    /**
     * msginitFile - inspect the supplied source file, capture gettext references
     * as a PoFile object
     *
     * @param string $filename name of php file
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
    public function msginitString($source, $refname)
    {
        if (!($this->poFile instanceof PoFile)) {
            $this->poFile = new PoFile;
        }

        $tpl = $this->smarty->createTemplate('eval:'.$source);
        $tags = $this->smarty->getTags($tpl);

        $translateTags = array_merge($this->gettextTags, $this->pgettextTags, $this->ngettextTags);
        foreach ($tags as $tag) {
            if (in_array($tag[0], $translateTags)) {
                $entry = new PoEntry;
                $haveEntry = false;
                $entry->add(PoTokens::REFERENCE, $refname);
                foreach ($tag[1] as $temp) {
                    foreach ($temp as $key => $value) {
                        if ($value[0]=="'" || $value[0]=='"') {
                            if (in_array($key, $this->msgidArgNames)) {
                                $entry->set(PoTokens::MESSAGE, $this->escapeForPo($value));
                                $haveEntry = true;
                            } elseif (in_array($key, $this->msgidPluralArgNames)) {
                                $entry->set(PoTokens::PLURAL, $this->escapeForPo($value));
                                $entry->set(PoTokens::FLAG, 'php-format');
                            } elseif (in_array($key, $this->msgctxtArgNames)) {
                                $entry->set(PoTokens::CONTEXT, $this->escapeForPo($value));
                            }
                        }
                    }
                }
                if ($haveEntry) {
                    $this->poFile->mergeEntry($entry);
                }
            }
        }
        return $this->poFile;
    }

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
        $string = stripcslashes($string);
        return addcslashes($string, "\0..\37\"");
    }
}
