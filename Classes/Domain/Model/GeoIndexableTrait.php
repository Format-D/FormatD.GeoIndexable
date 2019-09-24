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
	 * @return string
	 */
	public function getLocationAddress() {
		return $this->locationAddress;
	}

	public function setLocationDataDetails($details){
		$this->locationData = new LocationData($details);
	}

	/**
	 * @param $locationAddress
	 * @param bool $skipIndexing
	 */
	public function setLocationAddress($locationAddress, $skipIndexing = false) {
		$this->locationAddress = $locationAddress;
		if(!$skipIndexing){
			$this->onLocationChange($locationAddress);
		}
	}

	/**
	 * @param string $locationAddress
	 */
	protected function onLocationChange($locationAddress){
		if (strlen($locationAddress) > 1) {
			$this->geoIndexService->indexByAddress($this->locationData, $locationAddress);
		}
	}

}

?>
