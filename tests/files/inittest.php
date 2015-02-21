<?php

$translate = Translate::getInstance();

function gettext_noop($value)
{
    global $translate;
    return $translate->gettext_noop($value);
}

for ($number=6; $number>=0; $number--) {
    print sprintf(
        $translate->ngettext("%d pig went to the market.", "%d pigs went to the market.", $number),
        $number
    );
}

echo _('Short tag');

$translate->pgettext('tool', 'File');
// menu entry
$translate->pgettext('menu', 'File');

print "<pre>";
$strings = array(
    $translate->gettext_noop('Configuration'),
    gettext_noop (<<<'EOT'
Control Panel
with a lot of options
EOT
),
    // This is a comment for the translator
    gettext_noop("Could not install autorun file. Please try again."),
    /*
    this comment should be ignored
    */
    gettext_noop("Select a file or a folder"),
    /* refering to floppy disk drive */
    gettext_noop("When ejecting the drive, close the apps that are locking it"),
    gettext_noop("You do not have sufficient privileges for this operation."),
);
foreach ($strings as $s) {
    $t=$translate->gettext($s);
}

echo $translate->ngettext("%d pig went to the market.", "%d pigs went to the market.", 2);
