<?php

namespace FormatD\GeoIndexable\Domain\Service;

use FormatD\GeoIndexable\Domain\LocationData;
use FormatD\GeoIndexable\Domain\LocationDataDepr;
use FormatD\GeoIndexable\Domain\LocationDataDetails;
use FormatD\GeoIndexable\Domain\LocationDataInterface;
use FormatD\NeosUtilities\Eel\Helper\StringHelper;
use Neos\Flow\Annotations as Flow;

/**
 * Class AbstractGeoIndexingService
 * @package FormatD\GeoIndexable\Domain\Service
 * @Flow\Scope("singleton")
 */
abstract class AbstractGeoIndexingService
{

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var array
	 */
	protected $details;

	/**
	 * @param array $options
	 */
	public function setOptions(array $options): void {
		$this->options = $options;
	}

	/**
	 * @param LocationData $locationData
	 * @param string $address
	 * @return LocationData|null
	 */
	public function indexByAddress(LocationData $locationData, string $address): ?LocationData{
		$result = $this->getResultFromAddress($address);
		return $this->setResultToLocationData($locationData, $result);
	}

	/**
	 * Get the raw result as string for the address
	 *
	 * @param $address
	 * @return String
	 */
	abstract protected function getResultFromAddress($address): String;

	/**
	 * Maps the processed result to the provided LocationData-Object. Return NULL on invalid data
	 *
	 * @param $locationData
	 * @param $result
	 * @return LocationData|null
	 */
	abstract protected function setResultToLocationData(LocationData $locationData, $result): ?LocationData;

	/**
	 * @param $detail string
	 * @return bool
	 */
	public function providesDetail($detail) {
		return in_array($detail, $this->getDetails());
	}

	/**
	 * @param $details array
	 * @return bool
	 */
	public function providesDetails($details) {
		return $details == array_intersect($details, $this->getDetails()) ;
	}

	/**
	 * @return array
	 */
	public function getDetails(): array {
		return $this->details;
	}
}
