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

trait Redirecting
{
	/**
	 * Redirect to target media site version and localization version with path and query string.
	 * @param array $targetSystemParams 
	 * @return bool
	 */
	protected function redirectToVersion ($targetSystemParams) {
		$targetMediaSiteVersion = NULL;
		$targetLocalization = NULL;
		$mediaParamName = \MvcCore\Ext\Routers\IMedia::URL_PARAM_MEDIA_VERSION;
		$localizationParamName = \MvcCore\Ext\Routers\ILocalization::URL_PARAM_LOCALIZATION;
		$redirectStatusCode = \MvcCore\IResponse::MOVED_PERMANENTLY;
		if (isset($targetSystemParams[$mediaParamName])) 
			$targetMediaSiteVersion = $targetSystemParams[$mediaParamName];
		if (isset($targetSystemParams[$localizationParamName])) 
			$targetLocalization = $targetSystemParams[$localizationParamName];
		if ($targetMediaSiteVersion === NULL) {
			$redirectStatusCode = \MvcCore\IResponse::MOVED_PERMANENTLY;
			$targetMediaSiteVersion = $this->requestMediaSiteVersion !== NULL
				? $this->requestMediaSiteVersion
				: ($this->sessionMediaSiteVersion !== NULL
					? $this->sessionMediaSiteVersion
					: static::MEDIA_VERSION_FULL
				);
		}
		if ($targetLocalization === NULL) {
			$redirectStatusCode = \MvcCore\IResponse::SEE_OTHER;
			$targetLocalization = $this->requestLocalization !== NULL
				? $this->requestLocalization
				: ($this->sessionLocalization !== NULL
					? $this->sessionLocalization
					: $this->defaultLocalization
				);
		}

		// unset site key switch param and redirect to no switch param uri version
		$targetMediaUrlValue = $this->redirectMediaGetPrefixAndUnsetGet($targetMediaSiteVersion);
		$targetLocalizationUrlValue = $this->redirectLocalizationGetPrefixAndUnsetGet($targetLocalization);
		
		$request = & $this->request;
		if ($this->anyRoutesConfigured) {
			$requestPath = $this->request->GetPath(TRUE);
			$requestedPathIsHome = trim($requestPath, '/') === '' || $requestPath === $request->GetScriptName();

			$targetMediaPrefix = $targetMediaUrlValue === '' ? '' : '/' . $targetMediaUrlValue;

			if ($targetLocalizationUrlValue === $this->defaultLocalizationStr && $requestedPathIsHome) {
				$targetLocalizationPrefix = '';
			} else {
				$targetLocalizationPrefix = '/' . $targetLocalizationUrlValue;
			}

			if (
				$requestedPathIsHome &&
				$this->trailingSlashBehaviour === \MvcCore\IRouter::TRAILING_SLASH_REMOVE &&
				($targetMediaPrefix !== '' || $targetLocalizationPrefix !== '')
			) $requestPath = '';

			$targetUrl = $request->GetBaseUrl()
				. $targetMediaPrefix
				. $targetLocalizationPrefix
				. $requestPath;
		} else {
			$targetUrl = $request->GetBaseUrl();
			$this->removeDefaultCtrlActionFromGlobalGet();
			if ($this->requestGlobalGet)
				$targetUrl .= $request->GetScriptName();
		}
		if ($this->requestGlobalGet) {
			$amp = $this->getQueryStringParamsSepatator();
			$targetUrl .= '?' . str_replace('%2F', '/', http_build_query($this->requestGlobalGet, '', $amp, PHP_QUERY_RFC3986));
		}
		
		if ($this->request->GetFullUrl() === $targetUrl) return TRUE;
		
		$this->redirect($targetUrl, $redirectStatusCode);
		return FALSE;
	}
}
