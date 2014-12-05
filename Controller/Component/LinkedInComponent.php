<?php
App::import('LinkedIn.Lib', 'OAuthClient');
App::uses('Xml', 'Utility');

class LinkedInComponent extends Component {

	const AUTH_URL = 'https://www.linkedin.com/';
	const API_URL= 'http://api.linkedin.com/v1/';
	const SESSION_REQUEST = 'linkedin_request_token';
	const SESSION_ACCESS= 'linkedin_access_token';

	private $config = [
		'key' => null,
		'secret' => null,
		'scope' => 'r_basicprofile'
	];

	public function initialize(Controller $controller, $settings = array()){
		$this->Controller = $controller;
		$this->_set($settings);

		$this->config = array_merge($this->config, array_filter([
			'key' => Configure::read('LinkedIn.key'),
			'secret' => Configure::read('LinkedIn.secret'),
			'scope' => Configure::read('LinkedIn.scope')
		]));
	}

	public function connect($redirectUrl = null) {
		if ($redirectUrl === null) $redirectUrl = array('controller' => strtolower($this->Controller->name), 'action' => 'linkedin_connect');

		$requestToken = $this->createConsumer()->getRequestToken(self::AUTH_URL . 'uas/oauth/requestToken', Router::url($redirectUrl, true), 'POST', ['scope' => $this->config['scope']]);
		
		$this->Controller->Session->write(self::SESSION_REQUEST, serialize($requestToken));
		$this->Controller->redirect(self::AUTH_URL . 'uas/oauth/authorize?oauth_token=' . $requestToken->key);
	}

	public function authorize($redirectUrl = null) {
		if ($redirectUrl === null) $redirectUrl = array('controller' => strtolower($this->Controller->name), 'action' => 'linkedin_authorize');

		$requestToken = unserialize($this->Controller->Session->read(self::SESSION_REQUEST));

		$accessToken = $this->createConsumer()->getAccessToken(self::AUTH_URL . 'uas/oauth/accessToken', $requestToken);
		
		$this->Controller->Session->write(self::SESSION_ACCESS, serialize($accessToken));
		$this->Controller->redirect($redirectUrl);
	}

	public function call($path, $args) {
		$accessToken = unserialize($this->Controller->Session->read(self::SESSION_ACCESS));
		if ($accessToken === null) throw new InternalErrorException('LinkedIn: accessToken is empty');

		$result = $this->createConsumer()->get($accessToken->key, $accessToken->secret, self::API_URL . $path . $this->fieldSelectors($args));
		$response = Xml::toArray(Xml::build($result->body));

		if (isset($response['error'])) {
			throw new InternalErrorException('LinkedIn: '.$response['error']['message']);
		}

		return $response;
	}

	public function send($path, $data, $type = 'json') {
		switch ($type) {
			case 'json':
				$contentType = 'application/json';
				if (!is_string($data)) {
					$data = json_encode($data);
				}
				break;
			case 'xml':
				$contentType = 'text/xml';
				break;
			default:
				throw new InternalErrorException('LinkedIn: Type "' . $type . '" not supported');
		}

		$accessToken = $this->Controller->Session->read(self::SESSION_ACCESS);

		$responseText = $this->createConsumer()->postRaw($accessToken->key, $accessToken->secret, self::API_URL . $path, $data, $contentType);

		$response = Xml::toArray(Xml::build($result->body));

		if (isset($response['error'])) {
			throw new InternalErrorException('LinkedIn: '.$response['error']['message']);
		}

		return $response;
	}

	public function isConnected() {
		$accessToken = unserialize($this->Controller->Session->read(self::SESSION_ACCESS));

		return ($accessToken && is_object($accessToken));
	}

	private function createConsumer() {
		return new OAuthClient($this->config['key'], $this->config['secret']);
	}

	private function fieldSelectors($fields) {
		$result = '';

		if (!empty($fields)) {
			if (is_array($fields)) {
				foreach ($fields as $group => $field) {
					if (is_string($group)) {
						$fields[$group] = $group . $this->fieldSelectors($field);
					}
				}
				$fields = implode(',', $fields);
			}
			$result .= ':(' . $fields . ')';
		}

		return $result;
	}
}
