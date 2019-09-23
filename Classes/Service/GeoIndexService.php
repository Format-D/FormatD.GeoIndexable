<?php
namespace FormatD\GeoIndexable\Service;

/*
 * This file is part of the FormatD.GeoIndexable package.
 */

use FormatD\GeoIndexable\Domain\LocationData;
use FormatD\GeoIndexable\Domain\Service\AbstractGeoIndexingService;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Exception;

/**
 * Service for indexing geo-data
 *
 * @Flow\Scope("singleton")
 */
class GeoIndexService {

	/**
	 * @Flow\Inject
	 * @var \Neos\Flow\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @Flow\Inject
	 * @var \Neos\Flow\Http\Client\Browser
	 */
	protected $browser;

	/**
	 * @Flow\Inject
	 * @var \Neos\Flow\Http\Client\CurlEngine
	 */
	protected $requestEngine;

	/**
	 * The result of last index-call
	 *
	 * @var array
	 */
	protected $resultData = NULL;

	/**
	 * @var array<AbstractGeoIndexingService>
	 */
	protected $services = [];

	/**
	 * @var \Neos\Flow\ObjectManagement\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * GeoIndexService constructor.
	 * @param \Neos\Flow\ObjectManagement\ObjectManagerInterface $objectManager
	 */
	public function __construct(\Neos\Flow\ObjectManagement\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * init node context
	 */
	public function initializeObject() {
		$this->requestEngine->setOption(CURLOPT_TIMEOUT, 15);
		$this->browser->setRequestEngine($this->requestEngine);

		$conf = $this->configurationManager->getConfiguration(\Neos\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'FormatD.GeoIndexable');
		//TODO: check yaml felder if NULL etc.

		foreach($conf['services'] as $serviceName=>$serviceConf){
			$serviceClass = $serviceConf['serviceClass'];
			if(class_exists($serviceClass)){
				$this->services[$serviceName] = $this->objectManager->get($serviceClass);
				$this->services[$serviceName]->setOptions($serviceConf['options']);
			}else{
				//TODO: Errormeldung
			}
		}
	}

	/**
	 * @param LocationData $locationData
	 * @param $address
	 * @return LocationData|null
	 */
	public function indexByAddress(LocationData $locationData, $address){
		$geoService = $this->getServiceWithDetails($locationData->getDetails());
		if(!$geoService){
			return NULL;
		}
		return $geoService->indexByAddress($locationData, $address);
	}

	/**
	 * @param $details
	 * @return AbstractGeoIndexingService|null
	 */
	protected function getServiceWithDetails($details){
		$geoService = NULL;
		foreach($this->services as $service){
			if($service->providesDetails($details)){
				$geoService = $service;
				break;
			}
		}
		return $geoService;
	}

	/**
	 * @param $object
	 */
	/*
	public function setLocationDataOnObject($object) {
		if (isset($this->resultData->location->lng)) {
			$object->setLocationLatitude($this->resultData->location->lat);
			$object->setLocationLongitude($this->resultData->location->lng);
		}

		// Nominatim
		if (isset($this->resultData->lon)) {
			$object->setLocationLatitude($this->resultData->lat);
			$object->setLocationLongitude($this->resultData->lon);
			if (isset($this->resultData->timezone) && $this->resultData->timezone) {
				$object->setLocationTimezone($this->resultData->timezone);
			}
		}
		if (isset($this->resultData->address)) {
			$object->setLocationLabel(
				(isset($this->resultData->address->city) ? $this->resultData->address->city . ', ' :
					(isset($this->resultData->address->town) ? $this->resultData->address->town . ', ' :
						(isset($this->resultData->address->village) ? $this->resultData->address->village . ', ' : '')))
				. $this->resultData->address->country
			);
		} else {
			$object->setLocationLabel('');
		}
	}
	*/
}
