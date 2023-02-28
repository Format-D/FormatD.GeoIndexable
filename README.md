
# FormatD.GeoIndexable

Service for fetching geo-information for addresses in Neos Flow projects. By default, it uses the nominatim/openstreatmap and geonames API,
but you can use Google API, too.


## What does it do?

This package provides a service class for geo-indexing addresses. Also a php trait to use in domain models is provided.
This trait can be used to create a domain model that automatically fetches the geo location when the address is changed.


## Compatibility

Versioning scheme:

     1.0.0 
     | | |
     | | Bugfix Releases (non breaking)
     | Neos Compatibility Releases (non breaking except framework dependencies)
     Feature Releases (breaking)

Releases und compatibility:

| Package-Version | Neos Framework Version |
|-----------------|------------------------|
| 1.0.x           | deprecated             |
| 1.1.x           | deprecated             |
| 2.0.x           | 5.x, 6.x, 7.x, 8.x     |


## Configuration

Provide the geonames username (that you have to create on the geonames website) or deactivate the geonames api

```
FormatD:
  GeoIndexable:
    services:
      geonames:
        enabled: true
        options:
          username: ''
```

In case you want to use the Google API, you have to provide your API Key
```
FormatD:
  GeoIndexable:
    services:
      google:
        enabled: false
        options:
          apiKey: 'ichbineinapikey'
```



