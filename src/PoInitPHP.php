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
class PoInitPHP implements PoInitInterface
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
     * __construct
     * @param PoFile|null $poFile a PoFile object to be used in msginit
     */
    public function __construct(PoFile $poFile = null)
    {
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

        $source_lines = explode("\n", $source);
        //var_dump($source);
        $tokens = token_get_all($source);

        $translateTags = array_merge($this->gettextTags, $this->pgettextTags, $this->ngettextTags);
        $commentText=null;
        $commentLine=(-10);
        $tokenCount = count($tokens);
        $gtRefs = array();
        $i = 0;
        while ($i<$tokenCount) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] == T_STRING && in_array($token[1], $translateTags)) {
                $entry = new PoEntry;
                $gtt = array();
                list($id, $text, $line) = $token;
                $tname = token_name($id);
                $entry->add(PoTokens::REFERENCE, $refname . ':' . $line);
                $gtt['line']=$line;
                $gtt['function']=$text;
                $gtt['args'] = array();
                $la = 1;
                while (is_array($tokens[$i + $la]) &&  $tokens[$i + $la][0] == T_WHITESPACE) {
                    $la++;
                }
                if ($tokens[$i + $la] == '(') {
                    while ((')' != $token=$tokens[$i + $la]) && ($la < 10)) {
                        if (is_array($token) && (
                            $token[0] == T_CONSTANT_ENCAPSED_STRING
                            || $token[0] == T_ENCAPSED_AND_WHITESPACE
                        )) {
                            list($id, $text, $line) = $token;
                            $gtt['args'][]=$text;
                        }
                        $la++;
                    }
                    if (count($gtt['args'])) {
                        if (in_array($gtt['function'], $this->gettextTags)) {
                            $entry->set(PoTokens::MESSAGE, $this->escapeForPo($gtt['args'][0]));
                            $gtt['msgid'] = $this->escapeForPo($gtt['args'][0]);
                        } elseif (in_array($gtt['function'], $this->pgettextTags)) {
                            $entry->set(PoTokens::CONTEXT, $this->escapeForPo($gtt['args'][0]));
                            $entry->set(PoTokens::MESSAGE, $this->escapeForPo($gtt['args'][1]));
                            $gtt['msgctxt'] = $this->escapeForPo($gtt['args'][0]);
                            $gtt['msgid'] = $this->escapeForPo($gtt['args'][1]);
                        } elseif (in_array($gtt['function'], $this->ngettextTags)) {
                            $entry->set(PoTokens::MESSAGE, $this->escapeForPo($gtt['args'][0]));
                            $entry->set(PoTokens::PLURAL, $this->escapeForPo($gtt['args'][1]));
                            $entry->set(PoTokens::FLAG, 'php-format');
                            $gtt['msgid'] = $this->escapeForPo($gtt['args'][0]);
                            $gtt['msgid_plural'] = $this->escapeForPo($gtt['args'][1]);
                        }
                        if ($gtt['line']==($commentLine+1)) {
                            $entry->set(PoTokens::EXTRACTED_COMMENTS, $this->stripComment($commentText));
                            $gtt['comment'] = $commentText;
                        }
                        $this->poFile->mergeEntry($entry);
                    }
                }
            } elseif (is_array($token) && $token[0] == T_COMMENT) {
                list($id, $commentText, $commentLine) = $token;
            }
            $i++;
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

    /**
     * stripComment remove comment tags from string
     * @param string $string raw comment string
     * @return string
     */
    protected function stripComment($string)
    {
        return trim(str_replace(array('//','/*','*/'), '', $string));
    }
}
