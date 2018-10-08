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
	public function UrlByRoute (\MvcCore\IRoute & $route, & $params = [], $givenRouteName = 'self') {
		/** @var $route \MvcCore\Route */
		$defaultParams = $this->GetDefaultParams();
		$localizedRoute = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
		$mediaVersionUrlParam = static::MEDIA_VERSION_URL_PARAM;
		$localizationParamName = static::LOCALIZATION_URL_PARAM;

		if ($givenRouteName == 'self') {
			$newParams = [];
			foreach ($route->GetReverseParams() as $paramName) {
				$newParams[$paramName] = isset($params[$paramName])
					? $params[$paramName]
					: $defaultParams[$paramName];
			}
			if ($localizedRoute && isset($params[$localizationParamName])) 
				$newParams[$localizationParamName] = $params[$localizationParamName];
			if (isset($params[$mediaVersionUrlParam])) 
				$newParams[$mediaVersionUrlParam] = $params[$mediaVersionUrlParam];
			$params = $newParams;
			unset($params['controller'], $params['action']);
		}

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
			$params[static::SWITCH_MEDIA_VERSION_URL_PARAM] = $mediaSiteVersion;

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
			$params[static::SWITCH_LOCALIZATION_URL_PARAM] = $localizationStr;
		
		$result = $route->Url(
			$params, $defaultParams, $this->getQueryStringParamsSepatator()
		);
		//x([$result, $localizedRoute, $route, $filteredParams]);

		$localizationUrlPrefix = '';
		$questionMarkPos = mb_strpos($result, '?');
		$resultPath = $questionMarkPos !== FALSE 
			? mb_substr($result, 0, $questionMarkPos)
			: $result;
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
			$this->trailingSlashBehaviour === \MvcCore\IRouter::TRAILING_SLASH_REMOVE
		) $result = ltrim($result, '/');
		
		return $this->request->GetBasePath() 
			. $mediaSiteUrlPrefix 
			. $localizationUrlPrefix
			. $result;
	}
}
