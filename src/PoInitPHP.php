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
class PoInitPHP extends PoInitAbstract
{
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
                        } elseif (in_array($gtt['function'], $this->pgettextTags)) {
                            $entry->set(PoTokens::CONTEXT, $this->escapeForPo($gtt['args'][0]));
                            $entry->set(PoTokens::MESSAGE, $this->escapeForPo($gtt['args'][1]));
                        } elseif (in_array($gtt['function'], $this->ngettextTags)) {
                            $entry->set(PoTokens::MESSAGE, $this->escapeForPo($gtt['args'][0]));
                            $entry->set(PoTokens::PLURAL, $this->escapeForPo($gtt['args'][1]));
                            $entry->set(PoTokens::FLAG, 'php-format');
                        }
                        if ($gtt['line']==($commentLine+1)) {
                            $entry->set(PoTokens::EXTRACTED_COMMENTS, $this->stripComment($commentText));
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
     * stripComment remove comment tags from string
     * @param string $string raw comment string
     * @return string
     */
    protected function stripComment($string)
    {
        return trim(str_replace(array('//','/*','*/'), '', $string));
    }
}
