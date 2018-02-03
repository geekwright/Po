<?php
/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function._.php
 * Type:     function
 * Name:     _
 * Purpose:  translate a string
 * -------------------------------------------------------------
 */
function smarty_function__($params, Smarty_Internal_Template $template)
{
    $translate = new class {
        public function gettext(string $msgid): string
        {
            return $msgid;
        }
        public function pgettext(string $msgctxt, string $msgid): string
        {
            return $msgid;
        }
        public function ngettext(string $msgid, string $msgid_plural, int $num): string
        {
            $message = $num>1 ? $msgid_plural : $msgid;
            return sprintf($message, $num);
        }
    };

    if (isset($params['msgid'])) {
        $msgid = $params['msgid'];
        if (isset($params['msgid_plural'])) {
            $num = isset($params['num']) ? $params['num'] : 0;
            $temp = $translate->ngettext($msgid, $params['msgid_plural'], $num);
            $ret = sprintf($temp, $num);
        } elseif (isset($params['msgctxt'])) {
            $ret = $translate->pgettext($params['msgctxt'], $msgid);
        } else {
            $ret = $translate->gettext($msgid);
        }
        return $ret;
    }
    trigger_error("_: missing parameters");
    return '';
}
