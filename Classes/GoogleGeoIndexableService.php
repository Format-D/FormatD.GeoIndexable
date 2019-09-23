<?php

namespace FormatD\GeoIndexable;


use FormatD\GeoIndexable\Domain\LocationData;
use FormatD\GeoIndexable\Domain\LocationDataDepr;
use FormatD\GeoIndexable\Domain\LocationDataDetails;
use FormatD\GeoIndexable\Domain\LocationDataInterface;
use Neos\Flow\Annotations as Flow;
use FormatD\GeoIndexable\Domain\Service\AbstractGeoIndexingService;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Neos\Exception;

/**
 * Class GoogleGeoIndexableService
 * @package FormatD\GeoIndexable
 *
 * @Flow\Scope("singleton")
 */
class GoogleGeoIndexableService extends AbstractGeoIndexingService
{
	/**
	 * @Flow\Inject
	 * @var Browser
	 */
	protected $browser;

	/**
	 * @Flow\Inject
	 * @var CurlEngine
	 */
	protected $requestEngine;

	protected $details = [
		LocationDataDetails::LONGITUDE,
		LocationDataDetails::LATITUDE,
		LocationDataDetails::LABEL,
		LocationDataDetails::COUNTRY,
		LocationDataDetails::CITY,
	];

	public function initializeObject() {
		$this->requestEngine->setOption(CURLOPT_TIMEOUT, 15);
		$this->browser->setRequestEngine($this->requestEngine);
	}

	/**
	 * @param LocationData $locationData
	 * @param string $address
	 * @return LocationData|null
	 * @throws Exception
	 * @throws \Neos\Flow\Http\Client\InfiniteRedirectionException
	 */
	public function indexByAddress(LocationData $locationData, string $address): ?LocationData {
		$apiKey = $this->options['apiKey'];

		if(!$apiKey){
			throw new Exception('Please specify your Google Api Key!', 1567771297);
		}
		$formattedAddr = str_replace(' ','+', $address);
		$uri = $this->options['baseUri'].'geocode/json?address='.$formattedAddr.'&key='.$apiKey;
		$geoData = json_decode($this->browser->request($uri)->getContent());

		if ($geoData &&  array_key_exists(0, $geoData->results)) {
			$ret = $geoData->results[0];

			$addressData = $this->getAdressDataFromAddressComponents($ret->address_components);

			foreach($locationData->getDetails() as $detailName){
				switch ($detailName){
					case LocationDataDetails::LATITUDE:
						$locationData->latitude = $ret->geometry->location->lat;
						break;
					case LocationDataDetails::LONGITUDE:
						$locationData->longitude = $ret->geometry->location->lng;
						break;
					case LocationDataDetails::CITY:
						$locationData->city = $addressData['city'];
						break;
					case LocationDataDetails::LABEL:
						$locationData->label = $ret->formatted_address;
						break;
					case LocationDataDetails::COUNTRY:
						$locationData->country = $addressData['country'];
						break;
					default:
						throw new Exception('detail not supported');
						break;
				}
			}
			return $locationData;
		}
		return NULL;
	}

	protected function getAdressDataFromAddressComponents($components){
		$address = [];
		foreach($components as $component){
			if(in_array('country', $component['types'])){
				$address['country'] = $component['long_name'];
			}
		}
		return $address;
	}
}
