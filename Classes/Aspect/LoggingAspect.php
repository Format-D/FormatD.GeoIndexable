<?php


namespace FormatD\GeoIndexable\Aspect;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;

/**
 * An aspect
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class LoggingAspect
{
	/**
	 *
	 *
	 * @param JoinPointInterface $joinPoint
	 * @Flow\Before("method(.*->indexByAddress(.*)) && within(FormatD\GeoIndexable\Domain\Service\AbstractGeoIndexingService)")
	 * @return void
	 */
	public function logIndexByAddress(JoinPointInterface $joinPoint) {
		$className = $joinPoint->getClassName();
		echo 'Indexing by address with class: '.$className."\n";
	}
}
