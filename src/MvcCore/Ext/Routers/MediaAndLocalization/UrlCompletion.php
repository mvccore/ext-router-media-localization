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
	public function UrlByRoute (\MvcCore\IRoute & $route, & $params = []) {
		/** @var $route \MvcCore\Route */
		$requestedUrlParams = $this->GetRequestedUrlParams();
		$localizedRoute = $route instanceof \MvcCore\Ext\Routers\Localizations\Route;
		
		$mediaVersionUrlParam = static::MEDIA_VERSION_URL_PARAM;
		$localizationParamName = static::LOCALIZATION_URL_PARAM;

		if (isset($params[$mediaVersionUrlParam])) {
			$mediaSiteVersion = $params[$mediaVersionUrlParam];
			unset($params[$mediaVersionUrlParam]);
		} else if (isset($requestedUrlParams[$mediaVersionUrlParam])) {
			$mediaSiteVersion = $requestedUrlParams[$mediaVersionUrlParam];
			unset($requestedUrlParams[$mediaVersionUrlParam]);
		} else {
			$mediaSiteVersion = $this->mediaSiteVersion;
		}
		if (!isset($this->allowedSiteKeysAndUrlPrefixes[$mediaSiteVersion]))
			throw new \InvalidArgumentException(
				'['.__CLASS__.'] Not allowed media site version used to generate url: `'
				.$mediaSiteVersion.'`. Allowed values: `'
				.implode('`, `', array_keys($this->allowedSiteKeysAndUrlPrefixes)) . '`.'
			);

		if (isset($params[$localizationParamName])) {
			$localizationStr = $params[$localizationParamName];
			if ($localizedRoute) unset($params[$localizationParamName]);
		} else if (isset($requestedUrlParams[$localizationParamName])) {
			$localizationStr = $requestedUrlParams[$localizationParamName];
			if ($localizedRoute) unset($requestedUrlParams[$localizationParamName]);
		} else {
			$localizationStr = implode(
				static::LANG_AND_LOCALE_SEPARATOR, $this->localization
			);
		}
		if (!isset($this->allowedLocalizations[$localizationStr])) {
			if (isset($this->localizationEquivalents[$localizationStr])) 
				$localizationStr = $this->localizationEquivalents[$localizationStr];
			if (!isset($this->allowedLocalizations[$localizationStr]))
				throw new \InvalidArgumentException(
					'['.__CLASS__.'] Not allowed localization used to generate url: `'
					.$localizationStr.'`. Allowed values: `'
					.implode('`, `', array_keys($this->allowedLocalizations)) . '`.'
				);
		}

		if ($this->stricModeBySession && $mediaSiteVersion !== $this->mediaSiteVersion) 
			$params[static::SWITCH_MEDIA_VERSION_URL_PARAM] = $mediaSiteVersion;

		if (
			$this->stricModeBySession && 
			$localizationStr !== implode(static::LANG_AND_LOCALE_SEPARATOR, $this->localization)
		) 
			$params[static::SWITCH_LOCALIZATION_URL_PARAM] = $localizationStr;
		
		$result = $route->Url(
			$params, $requestedUrlParams, $this->getQueryStringParamsSepatator()
		);

		$mediaSiteUrlPrefix = $this->allowedSiteKeysAndUrlPrefixes[$mediaSiteVersion];

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

		if (
			$resultPathTrimmed === '' &&
			$mediaSiteUrlPrefix !== '' && 
			$this->trailingSlashBehaviour === \MvcCore\IRouter::TRAILING_SLASH_REMOVE
		) $result = ltrim($result, '/');
		
		return $this->request->GetBasePath() 
			. $mediaSiteUrlPrefix 
			. $localizationUrlPrefix
			. $result;
	}
}
