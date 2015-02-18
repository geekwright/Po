<?php

namespace Geekwright\Po;

/**
 * PoEntry - represent a single entry in a GNU gettext style PO or POT file
 *
 * @category  Po
 * @package   Po\PoEntry
 * @author    Richard Griffith <richard@geekwright.com>
 * @copyright 2015 Richard Griffith
 * @license   GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @link      https://github.com/geekwright/Po
 */
class PoEntry
{
    /**
     * @var array $entry
     */
    protected $entry = array();

    /**
     * __construct
     */
    public function __construct()
    {
        $this->entry[PoTokens::TRANSLATOR_COMMENTS] = null;
        $this->entry[PoTokens::EXTRACTED_COMMENTS] = null;
        $this->entry[PoTokens::REFERENCE] = null;
        $this->entry[PoTokens::FLAG] = null;
        $this->entry[PoTokens::PREVIOUS] = null;
        $this->entry[PoTokens::OBSOLETE] = null;
        $this->entry[PoTokens::CONTEXT] = null;
        $this->entry[PoTokens::MESSAGE] = null;
        $this->entry[PoTokens::PLURAL] = null;
        $this->entry[PoTokens::TRANSLATED] = null;
    }

    /**
     * add an entry to an array type entry
     * @param string $type  PoToken constant
     * @param string $value entry to store
     * @return void
     */
    public function add($type, $value)
    {
        if ($this->entry[$type] === null) {
            $this->entry[$type] = array();
        }
        if (is_scalar($this->entry[$type])) {
            $this->entry[$type] = array($this->entry[$type]);
        }
        $this->entry[$type][] = $value;
    }

    /**
     * add a quoted entry to an array type entry
     * @param string $type  PoToken constant
     * @param string $value entry to store
     * @return void
     */
    public function addQuoted($type, $value)
    {
        if ($value[0]=='"') {
            $value = substr($value, 1, -1);
        }
        $value = stripcslashes($value);

        if ($this->entry[$type] === null) {
            $this->entry[$type] = array();
        }
        if (is_scalar($this->entry[$type])) {
            $this->entry[$type] = array($this->entry[$type]);
        }
        $this->entry[$type][] = $value;
    }

    /**
     * add a quoted entry to a nested array
     *
     * This is mainly useful for translated plurals. Since any plural msgstr can have
     * continuation lines, the message is stored as an array of arrays.
     *
     * @param string  $type     PoToken constant
     * @param string  $value    entry to store
     * @param integer $position array position to store
     * @return void
     */
    public function addQuotedAtPosition($type, $value, $position)
    {
        if ($value[0]=='"') {
            $value = substr($value, 1, -1);
        }
        $value = stripcslashes($value);

        if ($this->entry[$type] === null) {
            $this->entry[$type] = array();
        }
        if (isset($this->entry[$type][$position]) &&
            is_scalar($this->entry[$type][$position])
        ) {
            $this->entry[$type][$position] = array($this->entry[$type][$position]);
        }
        $this->entry[$type][$position][] = $value;
    }

    /**
     * get any entry for a type
     * @param string $type PoToken constant
     * @return string|string[]|null
     */
    public function get($type)
    {
        return $this->entry[$type];
    }

    /**
     * get the entry for a type as a string
     * @param string $type PoToken constant
     * @return string|null
     */
    public function getAsString($type)
    {
        $ret = $this->entry[$type];
        if (is_array($ret)) {
            $ret = implode('', $ret);
        }
        return $ret;
    }

    /**
     * Get an entry as an array of strings. This is mainly for plural TRANSLATED
     * messages.
     *
     * @param string $type PoToken constant
     * @return string|null
     */
    public function getAsStringArray($type)
    {
        $plurals = $this->entry[$type];
        $plurals = is_array($plurals) ? $plurals : array('', '');
        $ret = array();
        foreach ($plurals as $i => $value) {
            if (is_array($value)) {
                $value = implode('', $value);
            }
            $ret[] = $value;
        }
        return $ret;
    }

    /**
     * set an entry to value
     * @param string $type  PoToken constant
     * @param string $value value to set
     * @return void
     */
    public function set($type, $value)
    {
        $this->entry[$type] = $value;
    }

    /**
     * Dump this entry as a po/pot file fragment
     * @return string
     */
    public function dumpEntry()
    {
        $commentKeys = array(
            PoTokens::TRANSLATOR_COMMENTS,
            PoTokens::EXTRACTED_COMMENTS,
            PoTokens::REFERENCE,
            PoTokens::FLAG,
            PoTokens::PREVIOUS,
            PoTokens::OBSOLETE,
        );

        $output = '';

        foreach ($commentKeys as $type) {
            $section = $this->entry[$type];
            if (is_array($section)) {
                foreach ($section as $comment) {
                    $output .= $type . ' ' . $comment . "\n";
                }
            } elseif (!($section === null)) {
                $output .= $type . ' ' . $section . "\n";
            }
        }
        $key = PoTokens::CONTEXT;
        if (!($this->entry[$key] === null)) {
            $output .= $key . $this->formatQuotedString($this->entry[$key]);
        }
        $key = PoTokens::MESSAGE;
        if (!($this->entry[$key] === null)) {
            $output .= $key . $this->formatQuotedString($this->entry[$key]);
            $key = PoTokens::PLURAL;
            if (!($this->entry[$key] === null)) {
                $output .= $key . $this->formatQuotedString($this->entry[$key]);
                $key = PoTokens::TRANSLATED;
                $plurals = $this->entry[$key];
                $plurals = is_array($plurals) ? $plurals : array('', '');
                foreach ($plurals as $i => $value) {
                    $output .= "{$key}[{$i}]" . $this->formatQuotedString($value);
                }
            } else {
                $key = PoTokens::TRANSLATED;
                $output .= $key . $this->formatQuotedString($this->entry[$key]);
            }
        }

        $output .= "\n";
        return $output;
    }

    /**
     * formatQuotedString - format a string for output by escaping control and
     * double quote characters, and surrouding with quotes
     * and double quo
     * @param string|null $value string to prepare
     * @param boolean     $bare  true for bare output, default false adds leading
     *                           space and trailing newline
     * @return string
     */
    protected function formatQuotedString($value, $bare = false)
    {
        if (is_array($value)) {
            $string = '';
            foreach ($value as $partial) {
                $string .= $this->formatQuotedString($partial, true) . "\n";
            }
            return $bare ? $string : ' ' . $string;
        } else {
            $string = ($value === null) ? '' : $value;
            $string = stripcslashes($string);
            $string = addcslashes($string, "\0..\37\"");
            $string = '"' . $string . '"';
            return $bare ? $string : ' ' . $string . "\n";
        }
    }

    /**
     * hasFlag - check for presence of a flag
     * @param string $name flag to check
     * @return string|false line containing the flag, or false if not found
     */
    public function hasFlag($name)
    {
        $flags = $this->entry[PoTokens::FLAG];
        $flags = is_array($flags) ? $flags : array();
        foreach ($flags as $flag) {
            if (false !== stripos($flag, $name)) {
                return $flag;
            }
        }
        return false;
    }
}
