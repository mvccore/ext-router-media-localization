# MvcCore - Extension - Router - Media & Localization

[![Latest Stable Version](https://img.shields.io/badge/Stable-v5.0.1-brightgreen.svg?style=plastic)](https://github.com/mvccore/ext-router-media-localization/releases)
[![License](https://img.shields.io/badge/License-BSD%203-brightgreen.svg?style=plastic)](https://mvccore.github.io/docs/mvccore/5.0.0/LICENSE.md)
![PHP Version](https://img.shields.io/badge/PHP->=5.4-brightgreen.svg?style=plastic)

MvcCore Router extension to manage website media version (full/tablet/mobile) for different templates/css/js files rendering and to manage your website language (or language and locale) version optionaly contained in url address in the beinning.  
This extension is mix of 2 extended router extensions:
- [`mvccore/ext-router-media`](https://github.com/mvccore/ext-router-media)
- [`mvccore/ext-router-localization`](https://github.com/mvccore/ext-router-localization)  

This extension does the same things as extensions above together.

## Installation
```shell
composer require mvccore/ext-router-media-localization
```

## Features
Extension has the same features as extensions bellow together:
- [Features for `mvccore/ext-router-media`](https://github.com/mvccore/ext-router-media#user-content-2-features)  
- [Features for `mvccore/ext-router-localization`](https://github.com/mvccore/ext-router-localization#user-content-2-features)  

In URL addresses is always contained media site prefix first (before localization prefix) like this - example:
- Full address for (default) locale `en-US`: `/en-US/any/path/with?query=string
- Mobile address for different locale `en-US`: `/m/de-DE/ein/route/mit?abfragezeichen=folge

## How It Works
Extension works in the same way as extensions named above, each task is executed  
like for media extension first, then like for localization extension as second:
- [How It Works - `mvccore/ext-router-media`](https://github.com/mvccore/ext-router-media#user-content-3-how-it-works)   
- [How It Works - `mvccore/ext-router-localization`](https://github.com/mvccore/ext-router-localization#user-content-3-how-it-works)  

Only routing is implemented explicitly (it means method `Route()` in this router) and processing redirections and completing url addresses is implemented explicitly - to complete the proper URL string together with both query string values or with both prefixes.

## Usage

### Usage - `Bootstrap` Initialization

Add this to `/App/Bootstrap.php` or to **very application beginning**, 
before application routing or any other extension configuration
using router for any purposes:

```php
$app = \MvcCore\Application::GetInstance();
$app->SetRouterClass('\MvcCore\Ext\Routers\MediaAndLocalization');
...
// to get router instance for next configuration:
/** @var $router \MvcCore\Ext\Routers\MediaAndLocalization */
$router = \MvcCore\Router::GetInstance();
```

All other specific usage and advanced configuration is the same as extensions bellow together:
- [More usage and configuration for `mvccore/ext-router-media`](https://github.com/mvccore/ext-router-media#user-content-42-usage---media-url-prefixes-and-allowed-media-versions)  
- [More usage and configuration for `mvccore/ext-router-localization`](https://github.com/mvccore/ext-router-localization#user-content-42-usage---default-localization)  
