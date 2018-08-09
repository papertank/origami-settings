<?php

namespace Origami\Settings;

use Origami\Settings\Settings;

trait HasSettings
{
    protected $settingsInstance;
    protected $settingsKey = 'settings';

    public function settings($key = null)
    {
        if (is_null($this->settingsInstance)) {
            $this->settingsInstance = new Settings($this->getAttributeValue($this->settingsKey), $this, $this->settingsKey);
        }

        if (! is_null($key)) {
            return $this->settingsInstance->get($key);
        }

        return $this->settingsInstance;
    }

    public function getSettingsConfig($identifier = null)
    {
        return [];
    }
}
