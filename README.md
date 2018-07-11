## Getting Started

Detect visitor is a library for detecting identity of browser, location, OS and device. 

### Installing

Do the following command:

```
composer require zulfikaradnan/visitor
```

After done installing Visitor, Download GeoLite2-City.mmdb from [here](http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz).
Or you can use GeoIP2 Maxmind premium.


### How to use

```
require('vendor/autoload.php');

use ZulfikarAdnan\Visitor\Device;
use ZulfikarAdnan\Visitor\GeoIP2;

$device = new Device(['devices']);
$geoip2 = new GeoIP2([
    'city' => 'GeoLite2-City.mmdb',
    // 'domain' => 'GeoIP2-Domain.mmdb',
    // 'isp' => 'GeoIP2-ISP',
    // 'connection_type' => 'GeoIP2-Connection-Type.mmdb',
]);

var_dump($device->toArray());

var_dump($geoip2->toArray());
```

### Output from use ZulfikarAdnan\Visitor\Device
```
{
    "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
    "device_type": "desktop",
    "device_brand": "unknown",
    "device_model": "unknown",
    "browser_name": "Chrome",
    "browser_short_name": "CH",
    "browser_version": "67.0.3396.99",
    "browser_engine": "Blink",
    "browser_engine_version": "unknown",
    "os_name": "Windows",
    "os_short_name": "WIN",
    "os_version": "10",
    "os_platform": "x64"
}
```


### Output from ZulfikarAdnan\Visitor\GeoIP2
```
{
    "ip_address": "114.14.11.1",
    "continent_geoname_id": 6255147,
    "continent_iso_code": "AS",
    "continent_name": "Asia",
    "country_geoname_id": 1643084,
    "country_iso_code": "ID",
    "country_confidence": "unknown",
    "country_name": "Indonesia",
    "subdivision_geoname_id": 1642907,
    "subdivision_iso_code": "JK",
    "subdivision_confidence": "unknown",
    "subdivision_name": "Jakarta",
    "city_geoname_id": 1642911,
    "city_confidence": "unknown",
    "city_name": "Jakarta",
    "average_income": "unknown",
    "accuracy_radius": 1000,
    "latitude": -6.1744,
    "longitude": 106.8294,
    "metro_code": "unknown",
    "population_density": "unknown",
    "postal_code": "unknown",
    "postal_confidence": "unknown",
    "domain": "indosat.com",
    "isp": "PT Indosat Tbk.",
    "organization": "PT Indosat Tbk.",
    "company_number": "unknown",
    "company_organization": "unknown",
    "connection_type": "Cellular"
}
```

### Thank you to :

- [Piwik Detector](https://github.com/matomo-org/device-detector)
- [GeoIp2](https://github.com/geoip2/geoip2)


## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details