<?php

namespace Geekwright\Po;

/**
 * PoTokens - constants representing line indentification tokens found in a
 * GNU gettext style PO or POT file
 *
 * @category  Po
 * @package   Po\PoTokens
 * @author    Richard Griffith <richard@geekwright.com>
 * @copyright 2015 Richard Griffith
 * @license   GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @link      https://github.com/geekwright/Po
 */
class PoTokens
{
    const TRANSLATOR_COMMENTS = '#';
    const EXTRACTED_COMMENTS = '#.';
    const REFERENCE = '#:';
    const FLAG = '#,';
    const PREVIOUS = '#|';
    const OBSOLETE = '#~';
    const CONTEXT = 'msgctxt';
    const MESSAGE = 'msgid';
    const PLURAL = 'msgid_plural';
    const TRANSLATED = 'msgstr';
    const TRANSLATED_PLURAL = 'msgstr[';
    const CONTINUED_DATA = '"';
}
