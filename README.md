# Po
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/geekwright/Po/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/geekwright/Po/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/geekwright/Po/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/geekwright/Po/?branch=master) [![Build Status](https://travis-ci.org/geekwright/Po.svg?branch=master)](https://travis-ci.org/geekwright/Po)

__Po__ is a set of objects to assist in reading, manipulating and creating GNU gettext style PO files.

## Installing
The recommended installation method is using composer. Include _"geekwright/po"_ in the _"require"_ section of your project's _composer.json_.
```
"require": {
  "geekwright/po": "1.0.*"
}
```
## Namespace
All __Po__ classes are in the Geekwright\Po namespace.

## Examples
__Po__ provides the capability to create, read, and modify PO and POT files, including the ability to scan PHP sources for gettext style calls to build a POT file. You can connect the pieces however you need, but here are a few examples for common situations.

### Reading a PO File
```PHP
    try {
        $poFile = new PoFile();
        $poFile->readPoFile('test.po');
        // list all the messages in the file
        $entries = $poFile->getEntries();
        foreach($entries as $entry) {
            echo $entry->getAsString(PoTokens::MESSAGE);
        }
    } catch (UnrecognizedInputException $e) {
        // we had unrecognized lines in the file, decide what to do
    } catch (FileNotReadableException $e) {
        // the file couldn't be read, nothing happened
    }

```

### Get the Plural-Forms Header
```PHP
    $pluralRule = $poFile->getHeaderEntry()->getHeader('plural-forms');
```

### Add a New Entry
```PHP
    $entry = new PoEntry;
    $entry->set(PoTokens::MESSAGE, 'This is a message.');
    $entry->set(PoTokens::FLAG, 'fuzzy');
    $poFile->addEntry($entry);
```

### Get the Translation for an Entry
The translation for an entry can be a string, or an array of strings if the Entry is a plural form. This code fragment will assign the translation to ```$msgstr``` appropriate for either case.
```PHP
    $msgid_plural = $entry->get(PoTokens::PLURAL);
    if (empty($msgid_plural)) {
        $msgstr = $entry->getAsString(PoTokens::TRANSLATED);
    } else {
        $msgstr = $entry->getAsStringArray(PoTokens::TRANSLATED);
    }
```

### Writing a PO File
```PHP
    try {
        $poFile->writePoFile('test.po');
    } catch (FileNotWriteableException $e) {
        // the file couldn't be written
    }
```

### Create a POT File from PHP sources
```PHP
    $poFile = new PoFile();
    $poInit = new PoInitPHP($poFile);
    foreach (glob("*.php") as $filename) {
        try {
            $poInit->msginitFile($filename);
        } catch (FileNotReadableException $e) {
            // the souce file couldn't be read, decide what to do
        }
    }
    try {
        $poFile->writePoFile('default.pot');
    } catch (FileNotWriteableException $e) {
        // the file couldn't be written
    }
```

## API
For more information, see the full Po [API documentation](http://geekwright.github.io/Po/api/).
