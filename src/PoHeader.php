<?php

namespace Geekwright\Po;

/**
 * A special PoEntry that represents the header of a GNU gettext style PO or POT file.
 * The header is the first entry of a PO file. It has an empty string as the "msgid"
 * value, and a set of structured strings compose the "msgstr" value. PoHeader exposes
 * these structured strings so that the individual values can be fetched or set by name.
 *
 * @category  Entries
 * @package   Po
 * @author    Richard Griffith <richard@geekwright.com>
 * @copyright 2015-2018 Richard Griffith
 * @license   GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @link      https://github.com/geekwright/Po
 */
class PoHeader extends PoEntry
{
    protected $structuredHeaders = null;


    /**
     * Create an empty header entry
     */
    public function __construct()
    {
        parent::__construct();
        $this->entry[PoTokens::MESSAGE] = "";
    }

    /**
     * Populate the internal structuredHeaders property with contents
     * of this entry's "msgstr" value.
     *
     * @return void
     */
    protected function buildStructuredHeaders(): void
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
     * Rebuild the this entry's "msgstr" value using contents of the internal
     * structuredHeaders property.
     *
     * @return boolean true if rebuilt, false if not
     */
    protected function storeStructuredHeader(): bool
    {
        if (is_null($this->structuredHeaders)) {
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
     * Get a header value string by key
     *
     * @param string $key case insensitive name of header to return
     *
     * @return string|null header string for key or false if not set
     */
    public function getHeader(string $key): ?string
    {
        $this->buildStructuredHeaders();
        $lkey = strtolower($key);
        $header = null;
        if (isset($this->structuredHeaders[$lkey]['value'])) {
            $header = $this->structuredHeaders[$lkey]['value'];
        }
        return $header;
    }

    /**
     * Set the value of a header string for a key.
     *
     * @param string $key   name of header to set. If the header exists, the name is
     *                      case insensitive. If it is new the given case will be used
     * @param string $value value to set
     *
     * @return void
     */
    public function setHeader(string $key, string $value): void
    {
        $this->buildStructuredHeaders();
        $lkey = strtolower($key);
        $newHeader = array('key' => $key, 'value' => $value);
        if (isset($this->structuredHeaders[$lkey])) {
            $newHeader = $this->structuredHeaders[$lkey];
            $newHeader['value'] = $value;
        }
        $this->structuredHeaders[$lkey] = $newHeader;
        $this->storeStructuredHeader();
    }

    /**
     * Set the POT-Creation-Date header
     *
     * @param integer|null $time unix timestamp, null to use current
     *
     * @return void
     */
    public function setCreateDate(?int $time = null): void
    {
        $this->setHeader('POT-Creation-Date', $this->formatTimestamp($time));
    }

    /**
     * Set the PO-Revision-Date header
     *
     * @param integer|null $time unix timestamp, null to use current
     *
     * @return void
     */
    public function setRevisionDate(?int $time = null): void
    {
        $this->setHeader('PO-Revision-Date', $this->formatTimestamp($time));
    }

    /**
     * Format a timestamp following PO file conventions
     *
     * @param integer|null $time unix timestamp, null to use current
     *
     * @return string formatted timestamp
     */
    protected function formatTimestamp(?int $time = null): string
    {
        if (empty($time)) {
            $time = time();
        }
        return gmdate('Y-m-d H:iO', $time);
    }

    /**
     * Create a default header entry
     *
     * @return void
     */
    public function buildDefaultHeader(): void
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
     *
     * @return string
     */
    public function dumpEntry(): string
    {
        $this->set(PoTokens::MESSAGE, "");
        return parent::dumpEntry();
    }
}
