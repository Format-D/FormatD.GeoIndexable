<?php

namespace FormatD\GeoIndexable\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Flowpack\ElasticSearch\Annotations as ElasticSearch;

/**
 * Trait to make a model geo-indexable
 */
trait GeoIndexableTrait {

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
	 * @var string
	 * @ElasticSearch\Indexable
	 */
	protected $locationAddress = '';

	/**
	 * @var float
	 * @ORM\Column(nullable=true)
	 * @ElasticSearch\Indexable
	 */
	protected $locationLatitude = NULL;

	/**
	 * @var float
	 * @ORM\Column(nullable=true)
	 * @ElasticSearch\Indexable
	 */
	protected $locationLongitude = NULL;

	/**
	 * @var string
	 * @ElasticSearch\Indexable
	 */
	protected $locationLabel = '';

	/**
	 * @var string
	 * @ORM\Column(nullable=true)
	 * @ElasticSearch\Indexable
	 */
	protected $locationTimezone = NULL;


	/**
	 * @Flow\Inject
	 * @var \FormatD\GeoIndexable\Service\GeoIndexService
	 */
	protected $geoIndexService;

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
	 * @return string
	 */
	public function getLocationAddress() {
		return $this->locationAddress;
	}

	/**
	 * @param string $locationAddress
	 */
	public function setLocationAddress($locationAddress) {
		$this->onLocationChange($locationAddress);
		$this->locationAddress = $locationAddress;
	}

	/**
	 * @return float
	 */
	public function getLocationLatitude() {
		return $this->locationLatitude;
	}

	/**
	 * @param float $locationLatitude
	 */
	public function setLocationLatitude($locationLatitude) {
		$this->locationGeoPoint['lat'] = $locationLatitude;
		$this->locationLatitude = $locationLatitude;
	}

	/**
	 * @return float
	 */
	public function getLocationLongitude() {
		return $this->locationLongitude;
	}

	/**
	 * @param float $locationLongitude
	 */
	public function setLocationLongitude($locationLongitude) {
		$this->locationGeoPoint['lon'] = $locationLongitude;
		$this->locationLongitude = $locationLongitude;
	}

	/**
	 * @return string
	 */
	public function getLocationLabel() {
		return $this->locationLabel;
	}

	/**
	 * @param string $locationLabel
	 */
	public function setLocationLabel($locationLabel) {
		$this->locationLabel = $locationLabel;
	}

	/**
	 * @return string
	 */
	public function getLocationTimezone() {
		return $this->locationTimezone;
	}

	/**
	 * @param string $locationTimezone
	 */
	public function setLocationTimezone($locationTimezone) {
		$this->locationTimezone = $locationTimezone;
	}


	/**
	 * @param string $locationAddress
	 */
	protected function onLocationChange($locationAddress){
		if (strlen($locationAddress) > 1) {
			$this->geoIndexService->indexAddress($locationAddress);
			$this->geoIndexService->setLocationDataOnObject($this);
		} else {
			$this->setLocationLabel('');
			$this->setLocationLatitude(NULL);
			$this->setLocationLongitude(NULL);
			$this->setLocationTimezone(NULL);
		}
	}

}

?>