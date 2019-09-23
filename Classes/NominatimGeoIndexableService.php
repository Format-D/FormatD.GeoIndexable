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
	 * @param LocationData $locationData
	 * @param string $address
	 * @return LocationData|null
	 */
	public function indexByAddress(LocationData $locationData, string $address): ?LocationData {
		$uri = $this->options['baseUri'] . 'search?format=json&addressdetails=1&accept-language=en&q=' . urlencode($address);
		$geoData = json_decode($this->sendRequest($uri), true);
		if ($geoData && array_key_exists(0, $geoData)) {
			$ret = $geoData[0];

			foreach($locationData->getDetails() as $detailName){
				switch ($detailName){
					case LocationDataDetails::LATITUDE:
						$locationData->latitude = $ret['lat'];
						break;
					case LocationDataDetails::LONGITUDE:
						$locationData->longitude = $ret['lon'];
						break;
					case LocationDataDetails::CITY:
						$locationData->city = isset($ret['address']['city']) ? $ret['address']['city'] : $ret['address']['county'];
						break;
					case LocationDataDetails::LABEL:
						$locationData->label = $ret['display_name'];
						break;
					case LocationDataDetails::COUNTRY:
						$locationData->country = $ret['address']['country'];
						break;
					case LocationDataDetails::BOUNDINGBOX:
						$locationData->boundingbox = $ret['boundingbox'];
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
}
