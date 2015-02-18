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

    protected function buildStructuredHeaders()
    {
        $this->structuredHeaders = array();
        $headers = $this->entry[PoTokens::TRANSLATED];
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

    public function setCreateDate($time = null)
    {
        $this->setHeader('POT-Creation-Date', $this->formatTimestamp($time));
    }

    public function setRevisionDate($time = null)
    {
        $this->setHeader('PO-Revision-Date', $this->formatTimestamp($time));
    }

    protected function formatTimestamp($time = null)
    {
        if (empty($time)) {
            $time = time();
        }
        return gmdate('Y-m-d H:iO', $time);
    }

    public function buildDefaultHeader()
    {
        $this->set(PoTokens::MESSAGE, "");
        $this->add(PoTokens::TRANSLATOR_COMMENTS, 'SOME DESCRIPTIVE TITLE');
        $this->add(PoTokens::TRANSLATOR_COMMENTS, 'Copyright (C) YEAR HOLDER');
        $this->add(PoTokens::TRANSLATOR_COMMENTS, 'LICENSE');
        $this->add(PoTokens::TRANSLATOR_COMMENTS, 'FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.');
        $this->add(PoTokens::TRANSLATOR_COMMENTS, '');
        $this->add(PoTokens::FLAG, 'fuzzy');
        $this->setHeader('Project-Id-Version', 'PACKAGE VERSION');
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
