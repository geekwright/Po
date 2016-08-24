<?php

namespace Geekwright\Po;

/**
 * PoEntry represent a single entry in a GNU gettext style PO or POT file.
 * An entry consists of an associative array of values, indexed by type. These
 * types are based on PO file line recognition tokens from PoTokens.
 *
 * @category  Entries
 * @package   Po
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
     * establish an empty entry
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
     * add a value to an array 'type' in the entry
     *
     * @param string $type  PoToken constant
     * @param string $value value to store
     * @return void
     */
    public function add($type, $value)
    {
        if ($this->entry[$type] === null) {
            $this->entry[$type] = array();
        }
        $this->entry[$type] = (array) $this->entry[$type];
        $this->entry[$type][] = $value;
    }

    /**
     * add a quoted value to the array 'type' in the entry
     *
     * @param string $type  PoToken constant
     * @param string $value value to store
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
        $this->entry[$type] = (array) $this->entry[$type];
        $this->entry[$type][] = $value;
    }

    /**
     * add a quoted value to the nested array 'type' in the entry
     *
     * This is mainly useful for translated plurals. Since any plural msgstr can have
     * continuation lines, the message is stored as an array of arrays.
     *
     * @param string  $type     PoToken constant
     * @param integer $position array position to store
     * @param string  $value    value to store
     * @return void
     */
    public function addQuotedAtPosition($type, $position, $value)
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
     * get the value for a specified type
     *
     * @param string $type PoToken constant
     *
     * @return string|string[]|null
     */
    public function get($type)
    {
        return $this->entry[$type];
    }

    /**
     * get the value of a specified type as a string
     *
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
     * Get the value of a specified type as an array of strings. This is
     * mainly for plural TRANSLATED messages.
     *
     * @param string $type PoToken constant
     *
     * @return string[]|null
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
            $ret[$i] = $value;
        }
        return $ret;
    }

    /**
     * set the value of a specified type
     *
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
     *
     * @return string
     */
    public function dumpEntry()
    {
        $output = $this->dumpEntryComments();

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
     * Dump the comments for this entry as a po/pot file fragment
     *
     * @return string
     */
    protected function dumpEntryComments()
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

        return $output;
    }

    /**
     * format a string for output by escaping control and double quote
     * characters, then surrounding with double quotes
     *
     * @param string|null $value string to prepare
     * @param boolean     $bare  true for bare output, default false adds leading
     *                           space and trailing newline
     *
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
     * check for presence of a flag
     *
     * @param string $name flag to check
     *
     * @return boolean true if flag is set, otherwise false
     */
    public function hasFlag($name)
    {
        $flags = array();
        $flagEntry = $this->entry[PoTokens::FLAG];
        if (!empty($flagEntry)) {
            foreach ((array) $flagEntry as $csv) {
                $temp = str_getcsv($csv, ',');
                foreach ($temp as $flag) {
                    $flag = strtolower(trim($flag));
                    $flags[$flag] = $flag;
                }
            }
            return isset($flags[strtolower(trim($name))]);
        }
        return false;
    }

    /**
     * add a flag to the entry
     *
     * @param string $name flag to check
     *
     * @return void
     */
    public function addFlag($name)
    {
        if (!$this->hasFlag($name)) {
            $flagEntry = $this->entry[PoTokens::FLAG];
            if ($flagEntry === null) {
                $this->set(PoTokens::FLAG, $name);
            } elseif (is_array($flagEntry)) {
                $flagEntry[] = $name;
                $this->set(PoTokens::FLAG, implode(',', $flagEntry));
            } else {
                $this->set(PoTokens::FLAG, $flagEntry . ',' . $name);
            }
        }
    }
}
