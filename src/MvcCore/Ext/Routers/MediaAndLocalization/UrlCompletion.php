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

trait UrlCompletion
{
	/**
	 * Complete non-absolute, non-localized url by route instance reverse info.
	 * If there is key `media_version` in `$params`, unset this param before
	 * route url completing and choose by this param url prefix to prepend 
	 * completed url string.
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
	 * @param string $givenRouteName
	 * @return string
	 */
	public function UrlByRoute (\MvcCore\IRoute & $route, array & $params = [], $givenRouteName = NULL) {
		/** @var $route \MvcCore\Route */
		$defaultParams = array_merge([], $this->GetDefaultParams() ?: []);
		if ($givenRouteName == 'self') 
			$params = array_merge($this->requestedParams ?: [], $params);
		
		$localizedRoute = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
		$mediaVersionUrlParam = static::URL_PARAM_MEDIA_VERSION;
		$localizationParamName = static::URL_PARAM_LOCALIZATION;

		if (isset($params[$mediaVersionUrlParam])) {
			$mediaSiteVersion = $params[$mediaVersionUrlParam];
			unset($params[$mediaVersionUrlParam]);
		} else if (isset($defaultParams[$mediaVersionUrlParam])) {
			$mediaSiteVersion = $defaultParams[$mediaVersionUrlParam];
			unset($defaultParams[$mediaVersionUrlParam]);
		} else {
			$mediaSiteVersion = $this->mediaSiteVersion;
		}
		
		if ($this->stricModeBySession && $mediaSiteVersion !== $this->mediaSiteVersion) 
			$params[static::URL_PARAM_SWITCH_MEDIA_VERSION] = $mediaSiteVersion;

		$routeMethod = $route->GetMethod();
		if ($routeMethod !== NULL && $routeMethod !== \MvcCore\IRequest::METHOD_GET && $this->routeGetRequestsOnly) {
			$mediaSiteUrlPrefix = '';
		} else if (isset($this->allowedSiteKeysAndUrlPrefixes[$mediaSiteVersion])) {
			$mediaSiteUrlPrefix = $this->allowedSiteKeysAndUrlPrefixes[$mediaSiteVersion];
		} else {
			$mediaSiteUrlPrefix = '';
			trigger_error(
				'['.__CLASS__.'] Not allowed media site version used to generate url: `'
				.$mediaSiteVersion.'`. Allowed values: `'
				.implode('`, `', array_keys($this->allowedSiteKeysAndUrlPrefixes)) . '`.',
				E_USER_ERROR
			);
		}

		if (isset($params[$localizationParamName])) {
			$localizationStr = $params[$localizationParamName];
			//if (!$localizedRoute) unset($params[$localizationParamName]);
		} else {
			$localizationStr = implode(
				static::LANG_AND_LOCALE_SEPARATOR, $this->localization
			);
			if ($localizedRoute) $params[$localizationParamName] = $localizationStr;
		}
		
		if (!isset($this->allowedLocalizations[$localizationStr])) {
			if (isset($this->localizationEquivalents[$localizationStr])) 
				$localizationStr = $this->localizationEquivalents[$localizationStr];
			if (!isset($this->allowedLocalizations[$localizationStr])) {
				$localizationStr = '';
				trigger_error(
					'['.__CLASS__.'] Not allowed localization used to generate url: `'
					.$localizationStr.'`. Allowed values: `'
					.implode('`, `', array_keys($this->allowedLocalizations)) . '`.',
					E_USER_ERROR
				);
			}
		}

		if (
			$this->stricModeBySession && 
			$localizationStr !== implode(static::LANG_AND_LOCALE_SEPARATOR, $this->localization)
		) 
			$params[static::URL_PARAM_SWITCH_LOCALIZATION] = $localizationStr;
		
		list($resultBase, $resultPathWithQuery) = $route->Url(
			$this->request, $params, $defaultParams, $this->getQueryStringParamsSepatator()
		);

		$localizationUrlPrefix = '';
		$questionMarkPos = mb_strpos($resultPathWithQuery, '?');
		$resultPath = $questionMarkPos !== FALSE 
			? mb_substr($resultPathWithQuery, 0, $questionMarkPos)
			: $resultPathWithQuery;
		$resultPathTrimmed = trim($resultPath, '/');
		if (
			$localizedRoute && !(
				$resultPathTrimmed === '' && 
				$localizationStr === $this->defaultLocalizationStr
			)
		) $localizationUrlPrefix = '/' . $localizationStr;
		if ($this->routeGetRequestsOnly) {
			$routeMethod = $route->GetMethod();
			if ($routeMethod !== NULL && $routeMethod !== \MvcCore\IRequest::METHOD_GET) 
				$localizationUrlPrefix = '';
		}

		if (
			$resultPathTrimmed === '' &&
			$this->trailingSlashBehaviour === \MvcCore\IRouter::TRAILING_SLASH_REMOVE &&
			($localizationUrlPrefix !== '' || $mediaSiteUrlPrefix !== '')
		) $resultPathWithQuery = ltrim($resultPathWithQuery, '/');
		
		return $resultBase
			. $mediaSiteUrlPrefix 
			. $localizationUrlPrefix
			. $resultPathWithQuery;
	}
}
