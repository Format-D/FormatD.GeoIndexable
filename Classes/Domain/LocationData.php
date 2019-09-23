<?php


namespace FormatD\GeoIndexable\Domain;


class LocationData implements LocationDataDetails
{
	/**
	 * @var array
	 */
	protected $details;

	/**
	 * LocationData constructor.
	 * @param $requiredDetails
	 */
	public function __construct($requiredDetails) {
		$this->details = $requiredDetails;
	}

	/**
	 * @return array
	 */
	public function getDetails(): array {
		return $this->details;
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public function getDetail($name){
		if(!in_array($name, $this->getDetails())){
			throw new \Exception('detail "'.$name.'" not available');
		}
		return $this->$name;
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value) {
		if(!in_array($name, $this->getDetails())){
			throw new \Exception('detail "'.$name.'" not available');
		}
		$this->$name = $value;
	}
}
