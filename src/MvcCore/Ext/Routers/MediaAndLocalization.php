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

namespace Mvccore\Ext\Routers;

class		MediaAndLocalization
extends		\MvcCore\Router
implements	\MvcCore\Ext\Routers\IMedia,
			\MvcCore\Ext\Routers\ILocalization,
			\MvcCore\Ext\Routers\IExtended
{
	use \MvcCore\Ext\Routers\Extended;

	use \MvcCore\Ext\Routers\Media\PropsGettersSetters;
	use \MvcCore\Ext\Routers\Media\Routing;

	use \MvcCore\Ext\Routers\Localization\PropsGettersSetters;
	use \MvcCore\Ext\Routers\Localization\Routing;
	
	use \MvcCore\Ext\Routers\MediaAndLocalization\Redirecting;
	use \MvcCore\Ext\Routers\MediaAndLocalization\UrlCompletion;

	const VERSION = '5.0.0-alpha';

	protected $trailingSlashBehaviour = 0;

	public function Route () {
		if (!$this->redirectToProperTrailingSlashIfNecessary()) return FALSE;
		$request = & $this->request;
		$requestCtrlName = $request->GetControllerName();
		$requestActionName = $request->GetActionName();
		$this->anyRoutesConfigured = count($this->routes) > 0;
		$this->preRoutePrepare();
		if (!$this->preRoutePrepareMedia()) return FALSE;
		if (!$this->preRoutePrepareLocalization()) return FALSE;
		if (!$this->preRouteMedia()) return FALSE;
		if (!$this->preRouteLocalization()) return FALSE;
		if ($requestCtrlName && $requestActionName) {
			$this->routeByControllerAndActionQueryString(
				$requestCtrlName, $requestActionName
			);
		} else {
			$this->routeByRewriteRoutes($requestCtrlName, $requestActionName);
			if ($this->currentRoute === NULL && !$this->requestLocalization) {
				$this->allowNonLocalizedRoutes = FALSE;
				if (!$this->checkLocalizationWithUrlAndRedirectIfNecessary()) 
					return FALSE;
			}
		}
		if ($this->currentRoute === NULL && (
			($request->GetPath() == '/' || $request->GetPath() == $request->GetScriptName()) ||
			$this->routeToDefaultIfNotMatch
		)) {
			list($dfltCtrl, $dftlAction) = $this->application->GetDefaultControllerAndActionNames();
			$this->SetOrCreateDefaultRouteAsCurrent(
				\MvcCore\Interfaces\IRouter::DEFAULT_ROUTE_NAME, $dfltCtrl, $dftlAction
			);
		}
		return $this->currentRoute instanceof \MvcCore\Interfaces\IRoute;
	}
}
