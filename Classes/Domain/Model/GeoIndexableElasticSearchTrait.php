<?php

namespace FormatD\GeoIndexable\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Flowpack\ElasticSearch\Annotations as ElasticSearch;
use FormatD\GeoIndexable\Domain\LocationDataDetails;

trait GeoIndexableElasticSearchTrait
{
	use GeoIndexableTrait;

	/**
	 * Lat and Lon as array for elasticsearch indexing [lat => xx.xxx, lon => xx.xxx]
	 * Be aware! After DB-Persist lat and lon are strings!
	 *
	 * @var array
	 * @ElasticSearch\Indexable
	 * @ElasticSearch\Mapping(type="geo_point")
	 */
	protected $locationGeoPoint = [];

	/**
	 * @return array
	 */
	public function getLocationGeoPoint() {
		if (isset($this->locationGeoPoint['lat']) && $this->locationGeoPoint['lat']) {
			$this->locationGeoPoint['lat'] = (float) $this->locationGeoPoint['lat'];
		}
		if (isset($this->locationGeoPoint['lon']) && $this->locationGeoPoint['lon']) {
			$this->locationGeoPoint['lon'] = (float) $this->locationGeoPoint['lon'];
		}
		return $this->locationGeoPoint;
	}

	/**
	 * @param string $locationAddress
	 */
	protected function onLocationChange($locationAddress){
		if (strlen($locationAddress) > 1) {
			$this->geoIndexService->indexByAddress($this->locationData, $locationAddress);
			if(
				$this->locationData->hasDetail(LocationDataDetails::LONGITUDE)
				&& $this->locationData->hasDetail(LocationDataDetails::LATITUDE)
			){
				$this->locationGeoPoint['lat'] = $this->locationData->getDetail(LocationDataDetails::LATITUDE);
				$this->locationGeoPoint['lon'] = $this->locationData->getDetail(LocationDataDetails::LONGITUDE);
			}
		}
	}
}
