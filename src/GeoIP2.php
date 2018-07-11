<?php

namespace ZulfikarAdnan\Visitor;

use GeoIp2\Database\Reader;

class GeoIP2
{
    const UNKNOWN                = 'unknown';
    const IP_ADDRESS             = 'ip_address';
    const CONTINENT_ID           = 'continent_geoname_id';
    const CONTINENT_ISOCODE      = 'continent_iso_code';
    const CONTINENT_NAME         = 'continent_name';
    const COUNTRY_ID             = 'country_geoname_id';
    const COUNTRY_ISOCODE        = 'country_iso_code';
    const COUNTRY_CONFIDENCE     = 'country_confidence';
    const COUNTRY_NAME           = 'country_name';
    const SUBDIVISION_ID         = 'subdivision_geoname_id';
    const SUBDIVISION_ISOCODE    = 'subdivision_iso_code';
    const SUBDIVISION_CONFIDENCE = 'subdivision_confidence';
    const SUBDIVISION_NAME       = 'subdivision_name';
    const CITY_ID                = 'city_geoname_id';
    const CITY_CONFIDENCE        = 'city_confidence';
    const CITY_NAME              = 'city_name';
    const AVERAGE_INCOME         = 'average_income';
    const ACCURATION_RADIUS      = 'accuracy_radius';
    const LATITUDE               = 'latitude';
    const LONGITUDE              = 'longitude';
    const METROCODE              = 'metro_code';
    const POPULATION_DENSITY     = 'population_density';
    const POSTAL_CODE            = 'postal_code';
    const POSTAL_CONFIDENCE      = 'postal_confidence';
    const DOMAIN                 = 'domain';
    const CONNECTION_TYPE        = 'connection_type';
    const ISP                    = 'isp';
    const ORGANIZATION           = 'organization';
    const COMPANY_NUMBER         = 'company_number';
    const COMPANY_ORGANIZATION   = 'company_organization';

    protected $config     = [];
    protected $attributes = [];

    /**
     * Create a new Device instance.
     *
     * @param array|null  $config
     * @param string|null $ipAddress
     */
    public function __construct(array $config = null, $ipAddress = null)
    {
        $this->setIpAddress($ipAddress);
        $this->setConfig($config);
    }

    /**
     * Set ip address
     *
     * @param string|null $ipAddress
     */
    public function setIpAddress($ipAddress = null)
    {
        if (!$ipAddress) {
            $ipAddress = array_get($_SERVER, 'HTTP_CLIENT_IP', array_get($_SERVER, 'HTTP_X_FORWARDED_FOR', array_get($_SERVER, 'HTTP_X_FORWARDED', array_get($_SERVER, 'HTTP_FORWARDED_FOR', array_get($_SERVER, 'HTTP_FORWARDED', array_get($_SERVER, 'REMOTE_ADDR', '127.0.0.1'))))));
        }

        $this->setAttribute(self::IP_ADDRESS, $ipAddress);

        return $this;
    }

    /**
     * Set config.
     *
     * @param array|null $config
     * @return $this
     */
    public function setConfig(array $config = null)
    {
        $this->config = $config;
        $this->setGeoIP2();

        return $this;
    }

    /**
     * Set geo from maxmind geoip2
     *
     * @return  $this
     */
    protected function setGeoIP2()
    {
        foreach ($this->config as $method => $file) {
            $method = ucwords(str_replace(['-', '_'], ' ', $method));
            $method = str_replace(' ', '', $method);

            if (method_exists($this, "getFromGeoIP2" . $method)) {
                $this->{"getFromGeoIP2" . $method}($file);
            }
        }

        return $this;
    }

    /**
     * Get city data from maxmind city
     *
     * @param  string $mmdb
     * @return $this
     */
    protected function getFromGeoIP2City($mmdb)
    {
        $reader = new Reader($mmdb);
        $record = $reader->city($this->{self::IP_ADDRESS});

        $attributes = [
            self::CONTINENT_ID           => $record->continent->geonameId,
            self::CONTINENT_ISOCODE      => $record->continent->code,
            self::CONTINENT_NAME         => $record->continent->name,
            self::COUNTRY_ID             => $record->country->geonameId,
            self::COUNTRY_ISOCODE        => $record->country->isoCode,
            self::COUNTRY_CONFIDENCE     => $record->country->confidence,
            self::COUNTRY_NAME           => $record->country->name,
            self::SUBDIVISION_ID         => $record->mostSpecificSubdivision->geonameId,
            self::SUBDIVISION_ISOCODE    => $record->mostSpecificSubdivision->isoCode,
            self::SUBDIVISION_CONFIDENCE => $record->mostSpecificSubdivision->confidence,
            self::SUBDIVISION_NAME       => $record->mostSpecificSubdivision->name,
            self::CITY_ID                => $record->city->geonameId,
            self::CITY_CONFIDENCE        => $record->city->confidence,
            self::CITY_NAME              => $record->city->name,
            self::AVERAGE_INCOME         => $record->location->averageIncome,
            self::ACCURATION_RADIUS      => $record->location->accuracyRadius,
            self::LATITUDE               => $record->location->latitude,
            self::LONGITUDE              => $record->location->longitude,
            self::METROCODE              => $record->location->metroCode,
            self::POPULATION_DENSITY     => $record->location->populationDensity,
            self::POSTAL_CODE            => $record->location->postalCode,
            self::POSTAL_CONFIDENCE      => $record->location->postalConfidence,
        ];

        $this->setAttributes($attributes);

        return $this;
    }

    /**
     * Get domain data from maxmind domain
     *
     * @param  string $mmdb
     * @return $this
     */
    protected function getFromGeoIP2Domain($mmdb)
    {
        $reader = new Reader($mmdb);
        $record = $reader->domain($this->{self::IP_ADDRESS});

        $this->setAttribute(self::DOMAIN, $record->domain);

        return $this;
    }

    /**
     * Get isp data from maxmind isp
     *
     * @param  string $mmdb
     * @return $this
     */
    protected function getFromGeoIP2Isp($mmdb)
    {
        $reader = new Reader($mmdb);
        $record = $reader->isp($this->{self::IP_ADDRESS});

        $attributes = [
            self::ISP                  => $record->isp,
            self::ORGANIZATION         => $record->organization,
            self::COMPANY_NUMBER       => $record->autonomousSystemNumber,
            self::COMPANY_ORGANIZATION => $record->autonomousSystemOrganization,
        ];

        $this->setAttributes($attributes);

        return $this;
    }

    /**
     * Get connection type data from maxmind connection type
     *
     * @param  string $mmdb
     * @return $this
     */
    protected function getFromGeoIP2ConnectionType($mmdb)
    {
        $reader = new Reader($mmdb);
        $record = $reader->connectionType($this->{self::IP_ADDRESS});

        $this->setAttribute(self::CONNECTION_TYPE, $record->connectionType);

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
