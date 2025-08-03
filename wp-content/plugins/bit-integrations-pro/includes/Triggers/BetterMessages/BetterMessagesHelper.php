<?php

namespace BitApps\BTCBI_PRO\Triggers\BetterMessages;

use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class BetterMessagesHelper
{
    public static function NewMessageReceivedFormatFields($message)
    {
        if (! \function_exists('Better_Messages')) {
            return;
        }

        $senderData = User::get($message->sender_id);
        $message = Better_Messages()->functions->get_message($message->id);

        $data = \is_object($message) ? get_object_vars($message) : $message;
        $data['sender'] = $senderData;

        return Helper::prepareFetchFormatFields($data);
    }

    public static function isPluginInstalled()
    {
        return class_exists('Better_Messages_Functions');
    }
}
