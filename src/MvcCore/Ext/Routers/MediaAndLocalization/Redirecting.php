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
	 * Response HTTP code, `303 See Other` for changing media, 
	 * `301 Moved Permanently` for localization. Default `0`.
	 * @var int
	 */
	protected $redirectStatusCode = 0;

	/**
	 * Redirect to target media site version with path and query string.
	 * @param string $targetMediaSiteVersion 
	 * @return bool
	 */
	protected function redirectToTargetMediaSiteVersion ($targetMediaSiteVersion) {
		$this->redirectStatusCode = \MvcCore\IResponse::SEE_OTHER;
		return $this->redirectToTargetVersion(
			$targetMediaSiteVersion, 
			$this->requestLocalization !== NULL
				? $this->requestLocalization
				: ($this->sessionLocalization !== NULL
					? $this->sessionLocalization
					: $this->defaultLocalization
				)
		);
	}

	/**
	 * Redirect to target localization version with path and query string.
	 * @param \string[] $targetLocalization 
	 * @return bool
	 */
	protected function redirectToTargetLocalization ($targetLocalization) {
		$this->redirectStatusCode = \MvcCore\IResponse::MOVED_PERMANENTLY;
		return $this->redirectToTargetVersion(
			$this->requestMediaSiteVersion !== NULL
				? $this->requestMediaSiteVersion
				: ($this->sessionMediaSiteVersion !== NULL
					? $this->sessionMediaSiteVersion
					: static::MEDIA_VERSION_FULL
				), 
			$targetLocalization
		);
	}

	/**
	 * Redirect to target media site version and localization version with path and query string.
	 * @param string $targetMediaSiteVersion 
	 * @param \string[] $targetLocalization 
	 * @return bool
	 */
	protected function redirectToTargetVersion ($targetMediaSiteVersion, $targetLocalization) {
		// unset site key switch param and redirect to no switch param uri version
		$request = & $this->request;
		$mediaVersionUrlParam = static::URL_PARAM_MEDIA_VERSION;
		$localizationUrlParam = static::URL_PARAM_LOCALIZATION;
		
		$targetMediaSameAsDefault = $targetMediaSiteVersion === static::MEDIA_VERSION_FULL;

		$targetLocalizationStr = implode(static::LANG_AND_LOCALE_SEPARATOR, $targetLocalization);
		$targetLocalizationSameAsDefault = $targetLocalizationStr === $this->defaultLocalizationStr;

		if (isset($this->requestGlobalGet[$mediaVersionUrlParam])) {
			if ($targetMediaSameAsDefault) {
				if (isset($this->requestGlobalGet[$mediaVersionUrlParam]))
					unset($this->requestGlobalGet[$mediaVersionUrlParam]);
			} else {
				$this->requestGlobalGet[$mediaVersionUrlParam] = $targetMediaSiteVersion;
			}
			$targetMediaPrefix = '';
		} else {
			$targetMediaPrefix = $this->allowedSiteKeysAndUrlPrefixes[$targetMediaSiteVersion];
		}

		if (isset($this->requestGlobalGet[$localizationUrlParam])) {
			if ($targetLocalizationSameAsDefault) {
				if (isset($this->requestGlobalGet[$localizationUrlParam]))
					unset($this->requestGlobalGet[$localizationUrlParam]);
			} else {
				$this->requestGlobalGet[$localizationUrlParam] = $targetLocalizationStr;
			}
			$targetLocalizationPrefix = '';
		} else {
			$path = $request->GetPath(TRUE);
			$targetLocalizationPrefix = (
				$targetLocalizationSameAsDefault && 
				(trim($path, '/') === '' || $path === $this->request->GetScriptName())
			)
				? ''
				: '/' . $targetLocalizationStr;
		}

		if ($this->anyRoutesConfigured) {
			$targetUrl = $request->GetBaseUrl()
				. $targetMediaPrefix
				. $targetLocalizationPrefix
				. $request->GetPath(TRUE);
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
		
		$this->redirect($targetUrl, $this->redirectStatusCode);
		return FALSE;
	}
}
