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
	 * @Flow\Around("method(FormatD\GeoIndexable\Service\GeoIndexService->sendRequest(.*))")
	 * @param JoinPointInterface $joinPoint The current joinpoint
	 * @return string
	 */
	public function cacheRequests(JoinPointInterface $joinPoint) {

		$cacheIdentifier = md5($joinPoint->getMethodArgument('uri'));

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
