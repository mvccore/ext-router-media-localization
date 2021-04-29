<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flidr (https://github.com/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENSE.md
 */

namespace MvcCore\Ext\Routers;

/**
 * Responsibility - recognize media site version and localization from URL or 
 *					user agent or session and set up request object, complete 
 *					automatically rewritten URL with remembered media site 
 *					version and localization. Redirect to proper media site 
 *					version or to localization by configuration. Than route 
 *					request like parent class does. Generate URL addresses with 
 *					prefixed media site version and with prefixed localization 
 *					for recognized special devices and for localized routes or 
 *					add only media site version or localization into query 
 *					string if necessary.
 */
class		MediaAndLocalization
extends		\MvcCore\Router
implements	\MvcCore\Ext\Routers\IMedia,
			\MvcCore\Ext\Routers\ILocalization,
			\MvcCore\Ext\Routers\IExtended {

	use \MvcCore\Ext\Routers\Extended;

	use \MvcCore\Ext\Routers\Media\Preparing;
	use \MvcCore\Ext\Routers\Media\PreRouting;
	use \MvcCore\Ext\Routers\Media\PropsGettersSetters;
	//use \MvcCore\Ext\Routers\Media\RedirectSections;
	//use \MvcCore\Ext\Routers\Media\Routing;
	//use \MvcCore\Ext\Routers\Media\UrlByRoute;
	//use \MvcCore\Ext\Routers\Media\UrlByRouteSections;
	use \MvcCore\Ext\Routers\Media\UrlByRouteSectionsMedia;

	use \MvcCore\Ext\Routers\Localization\Preparing;
	use \MvcCore\Ext\Routers\Localization\PreRouting;
	use \MvcCore\Ext\Routers\Localization\PropsGettersSetters;
	//use \MvcCore\Ext\Routers\Localization\RedirectSections;
	use \MvcCore\Ext\Routers\Localization\RewriteRouting;
	use \MvcCore\Ext\Routers\Localization\RewriteRoutingChecks;
	//use \MvcCore\Ext\Routers\Localization\Routing;
	//use \MvcCore\Ext\Routers\Localization\UrlByRoute;
	//use \MvcCore\Ext\Routers\Localization\UrlByRouteSections;
	use \MvcCore\Ext\Routers\Localization\UrlByRouteSectionsLocalization;
	
	use \MvcCore\Ext\Routers\MediaAndLocalization\RedirectSections;
	use \MvcCore\Ext\Routers\MediaAndLocalization\Routing;
	use \MvcCore\Ext\Routers\MediaAndLocalization\UrlByRoute;
	use \MvcCore\Ext\Routers\MediaAndLocalization\UrlByRouteSections;
	
	/**
	 * MvcCore Extension - Router - MediaAndLocalization - version:
	 * Comparison by PHP function version_compare();
	 * @see http://php.net/manual/en/function.version-compare.php
	 */
	const VERSION = '5.0.2';

}
