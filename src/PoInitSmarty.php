<?php

namespace Geekwright\Po;

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
 * A properly initialized Smarty v3 object  is required.
 *
 * @category  Extractors
 * @package   Po
 * @author    Richard Griffith <richard@geekwright.com>
 * @copyright 2015 Richard Griffith
 * @license   GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @link      https://github.com/geekwright/Po
 */
class PoInitSmarty extends PoInitAbstract
{
    /**
     * @var \Smarty $smarty Smarty 3 object
     */
    protected $smarty = null;

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
     * Set the Smarty and PoObjects to use in the Init process
     *
     * @param \Smarty     $smarty a fully initialize Smarty 3 instance
     * @param PoFile|null $poFile a PoFile object to be used in msginit
     */
    public function __construct(\Smarty $smarty, PoFile $poFile = null)
    {
        $this->smarty = $smarty;
        $this->poFile = $poFile;
    }

    /**
     * getMsgctxtArgNames - get argument name(s) used for the msgctxt
     *
     * @return string[]
     */
    public function getMsgctxtArgNames()
    {
        return $this->msgctxtArgNames;
    }

    /**
     * Set argument name(s) used for the msgctxt
     *
     * @param string[] $argNames array of argument names to set
     *
     * @return void
     */
    public function setMsgctxtArgNames($argNames)
    {
        $this->msgctxtArgNames = $argNames;
    }

    /**
     * Add argument name(s) used for the msgctxt
     *
     * @param string|string[] $argNames argument name(s) to add
     *
     * @return void
     */
    public function addMsgctxtArgNames($argNames)
    {
        $this->msgctxtArgNames = array_merge($this->msgctxtArgNames, (array) $argNames);
    }

    /**
     * Get argument name(s) used for the msgid
     *
     * @return string[]
     */
    public function getMsgidArgNames()
    {
        return $this->msgidArgNames;
    }

    /**
     * Set argument name(s) used for the msgid
     *
     * @param string[] $argNames array of argument names to set
     *
     * @return void
     */
    public function setMsgidArgNames($argNames)
    {
        $this->msgidArgNames = $argNames;
    }

    /**
     * Add argument name(s) used for the msgid
     *
     * @param string|string[] $argNames argument name(s) to add
     *
     * @return void
     */
    public function addMsgidArgNames($argNames)
    {
        $this->msgidArgNames = array_merge($this->msgidArgNames, (array) $argNames);
    }

    /**
     * Get argument name(s) used for the msgid_plural
     *
     * @return string[]
     */
    public function getMsgidPluralArgNames()
    {
        return $this->msgidPluralArgNames;
    }

    /**
     * Set argument name(s) used for the msgid_plural
     *
     * @param string[] $argNames array of argument names to set
     *
     * @return void
     */
    public function setMsgidPluralArgNames($argNames)
    {
        $this->msgidPluralArgNames = $argNames;
    }

    /**
     * Add argument name(s) used for the msgid_plural
     *
     * @param string|string[] $argNames argument name(s) to add
     *
     * @return void
     */
    public function addMsgidPluralArgNames($argNames)
    {
        $this->msgidPluralArgNames = array_merge($this->msgidPluralArgNames, (array) $argNames);
    }

    /**
     * Inspect the supplied source, capture gettext references as a PoFile object.
     *
     * @param string $source  php source code
     * @param string $refname source identification used for PO reference comments
     *
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
                            } elseif (in_array($key, $this->msgctxtArgNames)) {
                                $entry->set(PoTokens::CONTEXT, $this->escapeForPo($value));
                            }
                        }
                    }
                }
                if ($haveEntry) {
                    $this->checkPhpFormatFlag($entry);
                    $this->poFile->mergeEntry($entry);
                }
            }
        }
        return $this->poFile;
    }
}
