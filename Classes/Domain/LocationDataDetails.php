<?php


namespace FormatD\GeoIndexable\Domain;


interface LocationDataDetails
{
	/**
	 * Details
	 */
	const LATITUDE = 'latitude';
	const LONGITUDE = 'longitude';
	const LABEL = 'label';
	const CITY = 'city';
	const COUNTRY = 'country';
	const BOUNDINGBOX = 'boundingbox';
}
