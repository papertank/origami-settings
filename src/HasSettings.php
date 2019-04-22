<?php

namespace Origami\Settings;

trait HasSettings
{
    protected $settingsInstance;

    abstract public function save();

    public function getSettingsAttribute()
    {
        if (method_exists($this, 'getAttributeFromArray')) {
            $settings = $this->getAttributeFromArray('settings');
        } elseif (method_exists($this, 'getSettings')) {
            $settings = $this->getSettings();
        } else {
            throw new SettingsException(get_class($this).' should provide getSettings method');
        }

        if ($settings) {
            return is_array($settings) ? $settings : json_decode($settings, true);
        }

        return [];
    }

    public function setSettingsAttribute($settings)
    {
        if ($settings) {
            $settings = is_array($settings) ? $settings : json_decode($settings, true);
        }

        $this->attributes['settings'] = collect($this->settings()->merge($settings, false))->toJson();
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
