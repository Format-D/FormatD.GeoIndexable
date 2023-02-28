<?php
namespace FormatD\GeoIndexable\Aspect;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;

/**
 * An aspect which handles caching for all geoindex requests
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class RequestCachingAspect {

	/**
	 * @Flow\Inject
	 * @var \Neos\Cache\Frontend\StringFrontend
	 */
	protected $requestCache;

	/**
	 * @Flow\Around("method(.*->getResultFromAddress(.*)) && within(FormatD\GeoIndexable\Domain\Service\AbstractGeoIndexingService)")
	 * @param JoinPointInterface $joinPoint The current joinpoint
	 * @return string
	 */
	public function cacheResultFromAddress(JoinPointInterface $joinPoint){
		$address = $joinPoint->getMethodArgument('address');
		$className = $joinPoint->getClassName();
		$cacheIdentifier = md5($className.'-'.$address);

		if ($this->requestCache->has($cacheIdentifier)) {
			$response = $this->requestCache->get($cacheIdentifier);
		} else {
			$response = $joinPoint->getAdviceChain()->proceed($joinPoint);
			if ($response) {
				$this->requestCache->set($cacheIdentifier, $response, [], 0);
			}
		}

		return $response;
	}

}
