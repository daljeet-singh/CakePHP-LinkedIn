# CakePHP 2.x LinkedIn Plugin
This plugin provides a simple and managed way of connecting your CakePHP 2.x application to LinkedIn's public OAuth 2.0 API.

Built upon [CakePHP OAuth library](https://github.com/cakebaker/oauth-consumer) and [PHP OAuth library](https://oauth.googlecode.com/svn/code/php/), based on [inlet/CakePHP-LinkedIn](https://github.com/inlet/CakePHP-LinkedIn)


## Requirements

* PHP 5.3+
* CakePHP 2.1+

## Installation
[Composer](https://getcomposer.org/) is the recommend way of installing this plugin:
```bash
composer require easyrentcom/cakephp-linkedin:1.*@dev
```

Alternately clone the repository into your `app/Plugin/LinkedIn` directory:
```bash
git clone git://github.com/easyrentcom/CakePHP-LinkedIn.git app/Plugin/LinkedIn
```

## Setup
Load the plugin in your `app/Config/bootstrap.php` file and provide API details:
```php
//app/Config/bootstrap.php

CakePlugin::load('LinkedIn');

Configure::write([
	'LinkedIn' => [
		// Your LinkedIn application's API key and secret key
		// See https://www.linkedin.com/secure/developer
		'key' => 'API_KEY',
		'secret' => 'SECRET_KEY',
		// LinkedIn permission flags (separated with a space)
		// See https://developer.linkedin.com/documents/authentication#granting
		'scope' => 'r_basicprofile r_fullprofile' 
	]
]);
```

You will need to load the component in your controller before you can use it:
```php
var $components = array('LinkedIn.LinkedIn');
```

## Example Usage
```php
class LinkedinController extends AppController {
	var $components = array('LinkedIn.LinkedIn');

	public function index() {
		// Check if connected to LinkedIn
		if ($this->LinkedIn->isConnected()) {
			debug(
				// Print out user's profile data
				$this->LinkedIn->call('people/~', [
					'id',
					'picture-url',
					'first-name', 'last-name', 'summary', 'specialties', 'associations',
					'honors', 'interests', 'twitter-accounts',
					'positions' => ['title', 'summary', 'is-current', 'company'],
					'educations',
					'certifications',
					'skills' => ['id', 'skill', 'proficiency', 'years'],
					'recommendations-received'
				])
			);
		} else {
			echo('Not connected');
		}
	}

	// This route will redirect to LinkedIn's login page, collect a request token and
	// then redirect back to the route provided
	public function connect() {
		$this->LinkedIn->connect(['action' => 'authorize']);
	}

	// Here we convert the request token into a usable access token and redirect
	public function authorize() {
		$this->LinkedIn->authorize(['action' => 'index']);
	}
}
```

## Licence
The MIT License (MIT)

Copyright (c) 2014 Easyrent.com

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
