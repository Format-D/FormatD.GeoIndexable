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

/**
 * Class NominatimGeoIndexableService
 * @package FormatD\GeoIndexable
 *
 * @Flow\Scope("singleton")
 */
class NominatimGeoIndexableService extends AbstractGeoIndexingService
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

	/**
	 * @var array
	 */
	protected $details = [
		LocationDataDetails::LONGITUDE,
		LocationDataDetails::LATITUDE,
		LocationDataDetails::LABEL,
		LocationDataDetails::COUNTRY,
		LocationDataDetails::CITY,
		LocationDataDetails::BOUNDINGBOX,
	];

	public function initializeObject() {
		$this->requestEngine->setOption(CURLOPT_TIMEOUT, 15);
		$this->browser->setRequestEngine($this->requestEngine);
	}

	/**
	 * @param $uri
	 * @return string
	 * @throws \Neos\Flow\Http\Client\InfiniteRedirectionException
	 */
	protected function sendRequest($uri){
		$response = $this->browser->request($uri);
		return $response->getContent();
	}

	/**
	 * @param $address
	 * @return String
	 * @throws \Neos\Flow\Http\Client\InfiniteRedirectionException
	 */
	protected function getResultFromAddress($address): String {
		$uri = $this->options['baseUri'] . 'search?format=json&addressdetails=1&accept-language=en&q=' . urlencode($address);
		return $this->sendRequest($uri);
	}

	/**
	 * @param $locationData
	 * @param $result
	 * @return LocationData|null
	 */
	protected function setResultToLocationData(LocationData $locationData, $result): ?LocationData {
		$geoData = json_decode($result, true);
		if (!$geoData || !array_key_exists(0, $geoData)) {
			return NULL;
		}
		$data = $geoData[0];
		foreach($locationData->getRequiredDetails() as $detailName){
			switch ($detailName){
				case LocationDataDetails::LATITUDE:
					$locationData->setDetail($detailName, $data['lat']);
					break;
				case LocationDataDetails::LONGITUDE:
					$locationData->setDetail($detailName, $data['lon']);
					break;
				case LocationDataDetails::CITY:
					$value = isset($data['address']['city']) ? $data['address']['city'] : $data['address']['county'];
					$locationData->setDetail($detailName, $value);
					break;
				case LocationDataDetails::LABEL:
					$locationData->setDetail($detailName, $data['display_name']);
					break;
				case LocationDataDetails::COUNTRY:
					$locationData->setDetail($detailName, $data['address']['country']);
					break;
				case LocationDataDetails::BOUNDINGBOX:
					$locationData->setDetail($detailName, $data['boundingbox']);
					break;
				default:
					echo  "Detail '".$detailName."' not supported\n";
					return NULL;
					break;
			}
		}
		return $locationData;
	}

}
