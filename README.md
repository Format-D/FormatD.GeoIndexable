
# FormatD.GeoIndexable

Service for fetching geo-information for addresses in Neos Flow projects (using nominatim/openstreatmap and geonames api)


## What does it do?

This package provides a service class for geo-indexing addresses. Also a php trait to use in domain models is contained.
This trait can be used to create a domain model that automatically fetches the geo location when the address is changed.


## Configuration

Provide the geonames username (that you have to create on the geonames website) or deactivate the geonames api

```
FormatD:
  GeoIndexable:
    geoIndexService:
      geonamesEnable: true
      geonamesUsername: ''
```