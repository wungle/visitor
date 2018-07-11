<?php

namespace ZulfikarAdnan\Visitor;

use ArrayAccess;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

class Device implements ArrayAccess
{
    const UNKNOWN                = 'unknown';
    const USER_AGENT             = 'user_agent';
    const DEVICE_TYPE            = 'device_type';
    const DEVICE_BRAND           = 'device_brand';
    const DEVICE_MODEL           = 'device_model';
    const BROWSER_NAME           = 'browser_name';
    const BROWSER_SHORT_NAME     = 'browser_short_name';
    const BROWSER_VERSION        = 'browser_version';
    const BROWSER_ENGINE         = 'browser_engine';
    const BROWSER_ENGINE_VERSION = 'browser_engine_version';
    const OS_NAME                = 'os_name';
    const OS_SHORT_NAME          = 'os_short_name';
    const OS_VERSION             = 'os_version';
    const OS_PLATFORM            = 'os_platform';

    protected $piwik;
    protected $config;
    protected $attributes = [];

    /**
     * Create a new Device instance.
     *
     * @param array|null  $config
     * @param string|null $userAgent
     */
    public function __construct(array $config = null, $userAgent = null)
    {
        $this->setUserAgent($userAgent);
        $this->setConfig($config);
    }

    /**
     * Set user agent.
     *
     * @param string|null $userAgent
     * @return void
     */
    protected function setUserAgent($userAgent = null)
    {
        if (!$userAgent) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        $this->setAttribute(self::USER_AGENT, $userAgent);

        return $this;
    }

    /**
     * Set config.
     *
     * @param array|null $config
     * @return void
     */
    protected function setConfig(array $config = null)
    {
        $this->config = $config;
        $this->setDevice();

        return $this;
    }

    /**
     * Set device from Piwik Device Detector.
     *
     * @return  $this
     */
    protected function setDevice()
    {
        DeviceParserAbstract::setVersionTruncation(DeviceParserAbstract::VERSION_TRUNCATION_NONE);

        $this->piwik = new DeviceDetector($this->{self::USER_AGENT});
        $this->piwik->discardBotInformation();
        $this->piwik->skipBotDetection();
        $this->piwik->parse();

        foreach ($this->config as $method) {
            $method = ucwords(str_replace(['-', '_'], ' ', $method));
            $method = str_replace(' ', '', $method);

            if (method_exists($this, "getFromPiwik" . $method)) {
                $this->{"getFromPiwik" . $method}();
            }
        }

        return $this;
    }

    /**
     * Get device data from Piwik Device Detector.
     *
     * @return $this
     */
    protected function getFromPiwikDevice()
    {
        $type  = $this->piwik->getDeviceName();
        $brand = $this->piwik->getBrandName();
        $model = $this->piwik->getModel();

        $attributes = [
            self::DEVICE_TYPE  => $type,
            self::DEVICE_BRAND => $brand,
            self::DEVICE_MODEL => $model,
        ];

        $this->setAttributes($attributes);

        return $this;
    }

    /**
     * Get browser data from Piwik Device Detector.
     *
     * @return $this
     */
    protected function getFromPiwikBrowser()
    {
        $attributes = [
            self::BROWSER_NAME           => $this->piwik->getClient('name'),
            self::BROWSER_SHORT_NAME     => $this->piwik->getClient('short_name'),
            self::BROWSER_VERSION        => $this->piwik->getClient('version'),
            self::BROWSER_ENGINE         => $this->piwik->getClient('engine'),
            self::BROWSER_ENGINE_VERSION => $this->piwik->getClient('engine_version'),
        ];

        $this->setAttributes($attributes);

        return $this;
    }

    /**
     * Get Os data from Piwik Device Detector.
     *
     * @return $this
     */
    protected function getFromPiwikOs()
    {
        $attributes = [
            self::OS_NAME       => $this->piwik->getOs('name'),
            self::OS_SHORT_NAME => $this->piwik->getOs('short_name'),
            self::OS_VERSION    => $this->piwik->getOs('version'),
            self::OS_PLATFORM   => $this->piwik->getOs('platform'),
        ];

        $this->setAttributes($attributes);

        return $this;
    }

    /**
     * Set a given attribute on instance.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute($key, $value)
    {
        $value = $value ? $value : self::UNKNOWN;

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Set a given multiple attribute on instance.
     *
     * @param array|null $attributes
     * @param mixed $value
     */
    public function setAttributes(array $attributes = null)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Get an attribute from the instance.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return self::UNKNOWN;
    }

    /**
     * Convert the instance to array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Convert the instance to JSON.
     *
     * @param  integer $options
     * @return array
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        return $json;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Dynamically retrieve attributes on the instance.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the instance.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return !is_null($this->getAttribute($offset));
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Determine if an attribute or relation exists on the instatnce.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset an attribute on the instatnce.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Convert the instatnce to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
