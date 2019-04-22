<?php

namespace Origami\Settings;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class Settings
{
    protected $resource;
    protected $identifier;
    protected $settings = [];

    public function __construct($settings, Model $resource, $identifier = null)
    {
        $this->resource = $resource;
        $this->identifier = $identifier ?: 'settings';
        $this->init($settings);
    }

    protected function init($settings)
    {
        $this->settings = array_merge($this->defaults(), (array) $settings);
    }

    public function get($key)
    {
        return Arr::get($this->settings, $key);
    }

    public function set($key, $value)
    {
        $this->settings[$key] = $this->castSetting($key, $value);
    }

    public function merge(array $settings)
    {
        $this->settings = array_merge(
            $this->settings,
            $this->filterAndCast($settings)
        );

        return $this;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->settings);
    }

    public function all()
    {
        return $this->settings;
    }

    public function persist()
    {
        $this->resource->settings = $this->settings;
        $this->resource->save();

        return $this;
    }

    public function allowable()
    {
        return array_keys($this->config());
    }

    protected function filter(array $settings)
    {
        if ($allowable = $this->allowable()) {
            return Arr::only($settings, $allowable);
        }

        return $settings;
    }

    protected function cast(array $settings)
    {
        foreach ($settings as $key => $value) {
            $settings[$key] = $this->castSetting($key, $value);
        }

        return $settings;
    }

    protected function filterAndCast(array $settings)
    {
        return $this->cast($this->filter($settings));
    }

    protected function castSetting($key, $value)
    {
        $type = $this->config($key.'.type');

        if (is_null($type) || is_null($value)) {
            return $value;
        }

        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                if (is_string($value) && !is_numeric($value)) {
                    return strtolower($value) == 'true';
                }

                return (bool) $value;
            default:
                return $value;
        }
    }

    protected function config($key = null)
    {
        $config = method_exists($this->resource, 'getSettingsConfig') ?
                    $this->resource->getSettingsConfig($this->identifier) :
                    [];

        if (!is_null($key)) {
            return Arr::get($config, $key);
        }

        return $config;
    }

    protected function defaults()
    {
        $settings = new Collection($this->config());

        return $settings->filter(function ($setting) {
            return array_key_exists('default', $setting);
        })->map(function ($setting) {
            return $setting['default'];
        })->all();
    }

    public function toJson()
    {
        return json_encode($this->settings);
    }

    public function __get($key)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        throw new SettingsException("The {$key} setting does not exist.");
    }

    public function __toString()
    {
        return $this->toJson();
    }
}
