<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Routers\MediaAndLocalization;

trait UrlByRoute
{
	/**
	 * Complete non-absolute, localized or non-localized URL with special media 
	 * type prefix or without the prefix by route instance reverse info. 
	 * If there is key `media_version` in `$params`, unset this param before 
	 * route URL completing and choose by this param URL prefix to prepend 
	 * completed URL string.
	 * If there is key `localization` in `$params`, unset this param before
	 * route url completing and place this param as url prefix to prepend 
	 * completed url string and to prepend media site version prefix.
	 * Example:
	 *	Input (`\MvcCore\Route::$reverse`):
	 *		`"/products-list/<name>/<color>"`
	 *	Input ($params):
	 *		`array(
	 *			"name"			=> "cool-product-name",
	 *			"color"			=> "red",
	 *			"variant"		=> ["L", "XL"],
	 *			"media_version"	=> "mobile",
	 *			"localization"	=> "en-US",
	 *		);`
	 *	Output:
	 *		`/application/base-bath/m/en-US/products-list/cool-product-name/blue?variant[]=L&amp;variant[]=XL"`
	 * @param \MvcCore\Route|\MvcCore\IRoute &$route
	 * @param array $params
	 * @param string $urlParamRouteName
	 * @return string
	 */
	public function UrlByRoute (\MvcCore\IRoute $route, array & $params = [], $urlParamRouteName = NULL) {
		/** @var $this \MvcCore\Ext\Routers\MediaAndLocalization */
		// get domain with base path url section, 
		// path with query string url section 
		// and system params for url prefixes
		list($urlBaseSection, $urlPathWithQuerySection, $systemParams) = $this->urlByRouteSections(
			$route, $params, $urlParamRouteName
		);

		// remove localization prefix for non localized routes or
		// remove localization prefix if url targets top homepage `/` on default language version
		$localizedRoute = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
		$localizationParamName = static::URL_PARAM_LOCALIZATION;
		$urlPathWithQueryIsHome = NULL;
		if (isset($systemParams[$localizationParamName])) {
			if (!$localizedRoute) {
				unset($systemParams[$localizationParamName]);
			} else {
				// Get `TRUE` if path with query string target homepage - `/` (or `/index.php` - request script name)
				$urlPathWithQueryIsHome = $this->urlIsHomePath($urlPathWithQuerySection);
				if (
					$urlPathWithQueryIsHome && 
					$systemParams[$localizationParamName] === $this->defaultLocalizationStr
				) {
					unset($systemParams[$localizationParamName]);
				}
			}
		}
		
		// create prefixed url
		return $this->urlByRoutePrefixSystemParams(
			$urlBaseSection, $urlPathWithQuerySection, $systemParams, $urlPathWithQueryIsHome
		);
	}
}
