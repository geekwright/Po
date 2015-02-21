<?php

namespace Geekwright\Po;

/**
 * PoHeader - represent the header entry of a GNU gettext style PO or POT file
 *
 * @category  Po
 * @package   Po\PoHeader
 * @author    Richard Griffith <richard@geekwright.com>
 * @copyright 2015 Richard Griffith
 * @license   GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @link      https://github.com/geekwright/Po
 */
class PoHeader extends PoEntry
{
    protected $structuredHeaders = null;


    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->entry[PoTokens::MESSAGE] = "";
    }

    /**
     * buildStructuredHeaders - populate structuredHeaders property with contents
     * of this entry
     * @return void
     */
    protected function buildStructuredHeaders()
    {
        $this->structuredHeaders = array();
        $headers = $this->entry[PoTokens::TRANSLATED];
        $headers = ($headers === null) ? array() : $headers;
        $full = implode('', $headers);
        $headers = explode("\n", $full);
        // split on ':'
        $pattern = '/([a-z0-9\-]+):\s*(.*)/i';
        foreach ($headers as $h) {
            if (preg_match($pattern, trim($h), $matches)) {
                $this->structuredHeaders[strtolower($matches[1])] = array(
                    'key' => $matches[1],
                    'value' => $matches[2],
                );
            }
        }
    }

    /**
     * storeStructuredHeader - rebuild the PoTokens::TRANSLATED entry using
     * contents of the structuredHeaders property
     * @return boolean true if set, false if not
     */
    protected function storeStructuredHeader()
    {
        if (empty($this->structuredHeaders)) {
            return false;
        }
        $headers = array("");

        foreach ($this->structuredHeaders as $h) {
            $headers[] = $h['key'] . ': ' . $h['value'] . "\n";
        }
        $this->entry[PoTokens::TRANSLATED] = $headers;
        return true;
    }

    /**
     * getHeader - get a header string by case insensitive key
     * @param string $key name of header to return
     * @return string|false header string for key or false if not set
     */
    public function getHeader($key)
    {
        $this->buildStructuredHeaders();
        $lkey = strtolower($key);
        $header = false;
        if (isset($this->structuredHeaders[$lkey]['value'])) {
            $header = $this->structuredHeaders[$lkey]['value'];
        }
        return $header;
    }

    /**
     * setHeader - set a header string for a key
     * @param string $key   name of header to set
     * @param string $value value to set
     * @return void
     */
    public function setHeader($key, $value)
    {
        $this->buildStructuredHeaders();
        $lkey = strtolower($key);
        if (isset($this->structuredHeaders[$lkey])) {
            $this->structuredHeaders[$lkey]['value'] = $value;
        } else {
            $newHeader = array('key' => $key, 'value' => $value);
            $this->structuredHeaders[$lkey] = $newHeader;
        }
        $this->storeStructuredHeader();
    }

    /**
     * setCreateDate - set the POT-Creation-Date header
     * @param integer $time unix timestamp, null to use current
     * @return void
     */
    public function setCreateDate($time = null)
    {
        $this->setHeader('POT-Creation-Date', $this->formatTimestamp($time));
    }

    /**
     * setRevisionDate - set the PO-Revision-Date header
     * @param integer $time unix timestamp, null to use current
     * @return void
     */
    public function setRevisionDate($time = null)
    {
        $this->setHeader('PO-Revision-Date', $this->formatTimestamp($time));
    }

    /**
     * formatTimestamp - format a timestamp following PO file conventions
     * @param integer $time unix timestamp, null to use current
     * @return string formatted timestamp
     */
    protected function formatTimestamp($time = null)
    {
        if (empty($time)) {
            $time = time();
        }
        return gmdate('Y-m-d H:iO', $time);
    }

    /**
     * buildDefaultHeader - create a default header entry
     * @return void
     */
    public function buildDefaultHeader()
    {
        $this->set(PoTokens::MESSAGE, "");
        $this->set(PoTokens::TRANSLATOR_COMMENTS, 'SOME DESCRIPTIVE TITLE');
        $this->add(PoTokens::TRANSLATOR_COMMENTS, 'Copyright (C) YEAR HOLDER');
        $this->add(PoTokens::TRANSLATOR_COMMENTS, 'LICENSE');
        $this->add(PoTokens::TRANSLATOR_COMMENTS, 'FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.');
        $this->add(PoTokens::TRANSLATOR_COMMENTS, '');
        $this->set(PoTokens::FLAG, 'fuzzy');
        $this->setHeader('Project-Id-Version', 'PACKAGE VERSION');
        $this->setHeader('Report-Msgid-Bugs-To', 'FULL NAME <EMAIL@ADDRESS>');
        $this->setCreateDate();
        $this->setHeader('PO-Revision-Date', 'YEAR-MO-DA HO:MI+ZONE');
        $this->setHeader('Last-Translator', 'FULL NAME <EMAIL@ADDRESS>');
        $this->setHeader('Language-Team', 'LANGUAGE <EMAIL@ADDRESS>');
        $this->setHeader('MIME-Version', '1.0');
        $this->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->setHeader('Content-Transfer-Encoding', '8bit');
        $this->setHeader('Plural-Forms', 'nplurals=INTEGER; plural=EXPRESSION;');
        $this->setHeader('X-Generator', 'geekwright/po');
    }

    /**
     * Dump this entry as a po/pot file fragment
     * @return string
     */
    public function dumpEntry()
    {
        $this->set(PoTokens::MESSAGE, "");
        return parent::dumpEntry();
    }
}
