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
use Neos\FluidAdaptor\Tests\Functional\Form\Fixtures\Domain\Model\Location;
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
	 * @param $locationData
	 * @param $result
	 * @return mixed
	 * @throws Exception
	 */
	protected function setResultToLocationData(LocationData $locationData, $result): ?LocationData {
		$geoData = json_decode($result, true);
		if (!$geoData || !array_key_exists(0, $geoData)) {
			return NULL;
		}
		$data = $geoData->results[0];
		$addressData = $this->getAddressDataFromAddressComponents($data->address_components);

		foreach($locationData->getRequiredDetails() as $detailName){
			switch ($detailName){
				case LocationDataDetails::LATITUDE:
					$locationData->setDetail($detailName, $data->geometry->location->lat);
					break;
				case LocationDataDetails::LONGITUDE:
					$locationData->setDetail($detailName, $data->geometry->location->lng);
					break;
				case LocationDataDetails::CITY:
					$locationData->setDetail($detailName, $addressData['city']);
					break;
				case LocationDataDetails::LABEL:
					$locationData->setDetail($detailName, $data->formatted_address);
					break;
				case LocationDataDetails::COUNTRY:
					$locationData->setDetail($detailName, $addressData['country']);
					break;
				default:
					echo  "Detail '".$detailName."' not supported\n";
					return NULL;
					break;
			}
		}
		return $locationData;
	}

	/**
	 * @param $address
	 * @return |null
	 * @throws Exception
	 * @throws \Neos\Flow\Http\Client\InfiniteRedirectionException
	 */
	protected function getResultFromAddress($address): String {
		$apiKey = $this->options['apiKey'];

		if(!$apiKey){
			throw new Exception('Please specify your Google Api Key!', 1567771297);
		}
		$formattedAddr = str_replace(' ','+', $address);
		$uri = $this->options['baseUri'].'geocode/json?address='.$formattedAddr.'&key='.$apiKey;
		return $this->browser->request($uri)->getBody()->getContents();
	}

	protected function getAddressDataFromAddressComponents($components){
		$address = [];
		foreach($components as $component){
			if(in_array('country', $component['types'])){
				$address['country'] = $component['long_name'];
			}
		}
		return $address;
	}
}
