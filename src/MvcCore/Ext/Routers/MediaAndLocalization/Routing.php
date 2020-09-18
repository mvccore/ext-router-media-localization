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

trait Routing
{
	/**
	 * Route current app request by configured routes lists or by query string.
	 * 1. Check if request is targeting any internal action in internal ctrl.
	 * 2. Choose route strategy by request path and existing query string 
	 *    controller and/or action values - strategy by query string or by 
	 *    rewrite routes.
	 * 3. If request is not internal, redirect to possible better URL form by
	 *    configured trailing slash strategy and return `FALSE` for redirection.
	 * 4. Prepare media site version and localization properties and redirect 
	 *    if necessary by media configuration or by localization configuration.
	 * 5. Try to complete current route object by chosen strategy.
	 * 6. If there was not found any rewrite route in rewrite routes strategy, 
	 *    also if there is no localization in request, disallow non localized
	 *    route and re-call localization preparing method and redirect if 
	 *    necessary. It means any request path will be redirected into default 
	 *    localization.
	 * 7. If any current route found and if route contains redirection, do it.
	 * 8. If there is no current route and request is targeting homepage, create
	 *    new empty route by default values if ctrl configuration allows it.
	 * 9. If there is any current route completed, complete self route name by 
	 *    it to generate `self` routes and canonical URL later.
	 * 10.If there is necessary, try to complete canonical URL and if canonical 
	 *    URL is shorter than requested URL, redirect user to shorter version.
	 * If there was necessary to redirect user in routing process, return 
	 * immediately `FALSE` and return from this method. Else continue to next 
	 * step and return `TRUE`. This method is always called from core routing by:
	 * `\MvcCore\Application::Run();` => `\MvcCore\Application::routeRequest();`.
	 * @throws \LogicException Route configuration property is missing.
	 * @throws \InvalidArgumentException Wrong route pattern format.
	 * @return bool
	 */
	public function Route () {
		/** @var $this \MvcCore\Ext\Routers\MediaAndLocalization */
		$this->internalRequest = $this->request->IsInternalRequest();
		list($requestCtrlName, $requestActionName) = $this->routeDetectStrategy();
		if (!$this->internalRequest) {
			if (!$this->redirectToProperTrailingSlashIfNecessary()) return FALSE;
			$this->prepare();
			$this->prepareMedia();
			$this->prepareLocalization();
			if (!$this->preRouteMedia()) return FALSE;
			if (!$this->preRouteLocalization()) return FALSE;
		}
		if ($this->routeByQueryString) {
			$this->queryStringRouting(
				$requestCtrlName, $requestActionName
			);
		} else {
			$this->rewriteRouting($requestCtrlName, $requestActionName);
			if ($this->currentRoute === NULL && !$this->requestLocalization) {
				$this->allowNonLocalizedRoutes = FALSE;
				if (!$this->checkLocalizationWithUrlAndRedirectIfNecessary()) 
					return FALSE;
			}
		}
		if (!$this->routeProcessRouteRedirectionIfAny()) return FALSE;
		return $this->routeSetUpDefaultForHomeIfNoMatch()
					->routeSetUpSelfRouteNameIfAny()
					->canonicalRedirectIfAny();
	}
}
