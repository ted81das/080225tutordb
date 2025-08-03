<?php

if (!class_exists('BTCBI_Quiet_Installer_Skin')) {
    class BTCBI_Quiet_Installer_Skin extends \WP_Upgrader_Skin
    {
        // Suppress normal upgrader feedback / output
        public function feedback($string, ...$args)
        {
            // no output
        }
    }
}
