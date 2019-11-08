<?php

namespace FormatD\GeoIndexable\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use FormatD\GeoIndexable\Domain\LocationData;
use FormatD\GeoIndexable\Domain\LocationDataDetails;
use Neos\Flow\Annotations as Flow;
use Flowpack\ElasticSearch\Annotations as ElasticSearch;

/**
 * Trait to make a model geo-indexable
 */
trait GeoIndexableTrait {

	/**
	 * @Flow\Inject
	 * @var \FormatD\GeoIndexable\Service\GeoIndexService
	 */
	protected $geoIndexService;

	/**
	 * @var LocationData
	 * @ORM\Column(type="object")
	 */
	protected $locationData;

	/**
	 * @var string
	 */
	protected $locationAddress = '';

	/**
	 * @param array $details
	 */
	public function setLocationDataDetails($details) {
		$this->locationData = new LocationData($details);
	}

	/**
	 * @return string
	 */
	public function getLocationAddress() {
		return $this->locationAddress;
	}

	/**
	 * @param string $locationAddress
	 * @param bool $skipIndexing
	 */
	public function setLocationAddress($locationAddress, $skipIndexing = false) {
		$this->setLocationDataDetails([LocationDataDetails::LATITUDE, LocationDataDetails::LONGITUDE]);

		if ($this->locationAddress === $locationAddress) {
			$skipIndexing = true;
		} else {
			$this->locationAddress = $locationAddress;
		}
		if (!$skipIndexing) {
			$this->onLocationChange($locationAddress);
		}
	}

	/**
	 * @return LocationData
	 */
	public function getLocationData() {
		return $this->locationData;
	}

	/**
	 * @param string $locationAddress
	 */
	protected function onLocationChange($locationAddress) {
		if (strlen($locationAddress) > 1) {
			$this->geoIndexService->indexByAddress($this->locationData, $locationAddress);
		}
	}

}

?>
