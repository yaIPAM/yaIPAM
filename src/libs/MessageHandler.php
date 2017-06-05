<?php
/**
 * MessageHandler.php
 * Project: yaipam
 * User: ktammling
 * Date: 12.04.17
 * Time: 15:47
 */

class MessageHandler
{
    /**
     * @param string $header
     * @param string $message
     */
    public static function Success(string $header, string $message)
    {
        global $tpl;

        $tpl->assign(array(
            "S_MESSAGE_FILE"    =>  "error/success.html",
            "S_MESSAGE_HEADER"  =>  $header,
            "S_MESSAGE" =>  $message,
        ));
    }

    /**
     * @param string $header
     * @param string $message
     */
    public static function Error(string $header, string $message)
    {
        global $tpl;

        $tpl->assign(array(
            "S_MESSAGE_FILE"    =>  "error/error.html",
            "S_MESSAGE_HEADER"  =>  $header,
            "S_MESSAGE" =>  $message,
        ));
    }

    /**
     * @param string $header
     * @param string $message
     */
    public static function Warning(string $header, string $message)
    {
        global $tpl;

        $tpl->assign(array(
            "S_MESSAGE_FILE"    =>  "error/warning.html",
            "S_MESSAGE_HEADER"  =>  $header,
            "S_MESSAGE" =>  $message,
        ));
    }
}
