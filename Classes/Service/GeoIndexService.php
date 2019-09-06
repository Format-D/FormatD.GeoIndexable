<?php
namespace FormatD\GeoIndexable\Service;

/*
 * This file is part of the FormatD.GeoIndexable package.
 */

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
	 * @var string
	 */
	protected $nominatimBaseUri;

	/**
	 * @var boolean
	 */
	protected $geonamesEnable;

	/**
	 * @var string
	 */
	protected $geonamesBaseUri;

	/**
	 * @var string
	 */
	protected $geonamesUsername;

	/**
	 * @var string
	 */
	protected $googleBaseUri;

	/**
	 * @var string
	 */
	protected $googleApiKey;

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
	 * init node context
	 */
	public function initializeObject() {
		$this->requestEngine->setOption(CURLOPT_TIMEOUT, 15);
		$this->browser->setRequestEngine($this->requestEngine);

		$conf = $this->configurationManager->getConfiguration(\Neos\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'FormatD.GeoIndexable.geoIndexService');
		$this->nominatimBaseUri = $conf['nominatimBaseUri'];
		$this->geonamesEnable = $conf['geonamesEnable'];
		$this->geonamesBaseUri = $conf['geonamesBaseUri'];
		$this->geonamesUsername = $conf['geonamesUsername'];
		$this->googleBaseUri = $conf['googleBaseUri'];
		$this->googleApiKey = $conf['googleApiKey'];
	}

	/**
	 * @param $address
	 * @param string $serviceToUse
	 * @return bool
	 * @throws Exception
	 * @throws \Neos\Flow\Http\Client\InfiniteRedirectionException
	 */
	public function indexAddress($address, $serviceToUse = 'nominatim') {
		switch ($serviceToUse) {
			case 'google':
				$indexedAddress = $this->indexWithGoogle($address);
				break;
			default:
				$indexedAddress = $this->indexWithNomination($address);
		}
		return $indexedAddress;
	}

	/**
	 * @param $address
	 * @return bool
	 * @throws \Neos\Flow\Http\Client\InfiniteRedirectionException
	 */
	public function indexWithNomination($address)
	{
		$uri = $this->nominatimBaseUri . 'search?format=json&addressdetails=1&accept-language=en&q=' . urlencode($address);
		$geoData = json_decode($this->sendRequest($uri));
		if ($geoData && array_key_exists(0, $geoData)) {
			if ($this->geonamesEnable && isset($geoData[0]->lon)) {
				$timeZoneServiceUrl = $this->geonamesBaseUri . "timezoneJSON?lat=" . $geoData[0]->lat . "&lng=".$geoData[0]->lon . "&username=" . $this->geonamesUsername;
				$timeZone = json_decode($this->sendRequest($timeZoneServiceUrl), true);
				$geoData[0]->timezone = $timeZone['timezoneId'];
			}
			$this->resultData = $geoData[0];
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @param $address
	 * @return bool
	 * @throws Exception
	 * @throws \Neos\Flow\Http\Client\InfiniteRedirectionException
	 */
	public function indexWithGoogle($address)
	{
		if(!$this->googleApiKey){
			throw new Exception('Please specify your Google Api Key!', 1567771297);
		}
		$formattedAddr = str_replace(' ','+', $address);
		$uri = $this->googleBaseUri . 'geocode/json?address='.$formattedAddr.'&key='.$this->googleApiKey;
		$geoData = json_decode($this->browser->request($uri)->getContent());

		if ($geoData &&  array_key_exists(0, $geoData->results)) {
			$this->resultData = $geoData->results[0]->geometry;
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @param $uri
	 * @return string
	 * @throws \Neos\Flow\Http\Client\InfiniteRedirectionException
	 */
	protected function sendRequest($uri) {
		$response = $this->browser->request($uri);
		return $response->getContent();
	}

	/**
	 * @param $object
	 */
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

	/**
	 * @return float
	 */
	public function getLongitude() {
		if (isset($this->resultData->location->lng)) {
			return $this->resultData->location->lng;
		} elseif (isset($this->resultData->lon)) {
			return $this->resultData->lon;
		}
		return NULL;
	}

	/**
	 * @return float
	 */
	public function getLatitude() {
		if (isset($this->resultData->location->lat)) {
			return $this->resultData->location->lat;
		} elseif (isset($this->resultData->lat)) {
			return $this->resultData->lat;
		}
		return NULL;
	}
}