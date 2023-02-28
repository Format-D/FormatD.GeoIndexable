<?php


namespace FormatD\GeoIndexable\Domain;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("prototype")
 */
class LocationData implements LocationDataDetails
{
	/**
	 * @var array
	 */
	protected $details;

	/**
	 * @param array $requiredDetails
	 */
	public function __construct($requiredDetails) {
		$this->details = array_combine($requiredDetails, array_pad([], count($requiredDetails), ''));
	}

	/**
	 * @return array
	 */
	public function getRequiredDetails(){
		return array_keys($this->getDetails());
	}

	/**
	 * @return array
	 */
	public function getDetails(): array {
		return $this->details;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasDetail($name){
		return array_key_exists($name, $this->getDetails());
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public function getDetail($name){
		if(!$this->hasDetail($name)){
			return NULL;
		}
		return $this->details[$name];
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return bool (true if successful and false if no detail was found)
	 */
	public function setDetail($name, $value){
		if(!$this->hasDetail($name)){
			return false;
		}
		$this->details[$name] = $value;
		return true;
	}
}
