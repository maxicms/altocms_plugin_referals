<?php

    if (!class_exists('Plugin')) {
        die('Hacking attemp!');
    }

    class PluginReferals extends Plugin
    {
        public $aInherits = array(
            'action' => array('ActionRegistration')
        );

        public function Activate()
        {
            $this->ExportSQL(__DIR__ . '/sql/activate.sql');
            return true;
        }

        public function Deactivate()
        {
            $this->ExportSQL(__DIR__ . '/sql/deactivate.sql');
            return true;
        }

        public function Init()
        {
            return true;
        }
    }
