<?php

namespace Origami\Settings;

trait HasSettings
{
    protected $settingsInstance;

    abstract public function save();

    abstract public function getAttributeValue();

    abstract public function setAttribute();

    public function getSettingsAttribute()
    {
        if ($settings = $this->getAttributeValue('settings')) {
            return is_array($settings) ? $settings : json_decode($settings, true);
        }

        return [];
    }

    public function setSettingsAttribute($settings)
    {
        $this->setAttribute(
            'settings',
            collect($this->settings()->merge($settings, false))->toJson()
        );
    }

    public function settings()
    {
        if (is_null($this->settingsInstance)) {
            $this->settingsInstance = new Settings($this->getSettingsAttribute(), $this);
        }

        return $this->settingsInstance;
    }

    public function setting($key, $fallback = null)
    {
        return $this->settings()->get($key, $fallback);
    }

    public function getSettingsConfig()
    {
        return [];
    }
}
