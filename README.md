# CakePHP 2.x LinkedIn Plugin
* License: MIT

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
		'key' => 'API_KEY',
		'secret' => 'SECRET_KEY',
		'scope' => 'r_basicprofile r_fullprofile' // LinkedIn permission flags
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
					'positions' => ['title', 'summary', 'start-date', 'end-date', 'is-current', 'company'],
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
