<?php

if (!class_exists('BTCBI_Quiet_Upgrader_Skin')) {
    class BTCBI_Quiet_Upgrader_Skin extends \WP_Upgrader_Skin
    {
        // Suppress normal upgrader feedback / output
        public function feedback($string, ...$args)
        {
            // no output
        }
    }
}
