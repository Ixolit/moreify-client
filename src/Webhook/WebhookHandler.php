<?php

namespace Ixolit\Moreify\Webhook;

use Ixolit\Moreify\Webhook\Exception\ContentTypeNotSupportedException;
use Ixolit\Moreify\Webhook\Exception\EndpointNotFoundException;
use Ixolit\Moreify\Webhook\Exception\InvalidMethodException;
use Ixolit\Moreify\Webhook\Exception\InvalidSignatureException;
use Ixolit\Moreify\Webhook\Exception\WebhookException;
use Ixolit\Moreify\Webhook\Parser\ParserInterface;
use Ixolit\Moreify\Webhook\Parser\JsonParser;

/**
 * Class WebhookHandler
 * @package Ixolit\Moreify\Webhook
 */
class WebhookHandler implements WebhookHandlerInterface {

	/**
	 * @var WebhookEndpoint[]
	 */
	private $endpoints = array();

	/**
	 * Add webhook endpoint credentials
	 * @param WebhookEndpoint $endpoint
	 * @return $this
	 */
	public function addWebhookEndpoint(WebhookEndpoint $endpoint) {
		$this->endpoints[$endpoint->getId()] = $endpoint;
		return $this;
	}

	/**
	 * Retrieves request data, validates and returns a Webhook wrapper object which holds
	 * the actual Event and its data
	 * @return Webhook
	 * @throws WebhookException
	 */
	public function handle() {

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			throw new InvalidMethodException('HTTP methods other than POST are not valid');
		}

		$endpointId = @$_SERVER['HTTP_X_MOREIFY_WEBHOOK_ENDPOINT_ID'];

		$url = $this->getSchema() . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		$body = @file_get_contents('php://input');

		$algo = @$_SERVER['HTTP_X_MOREIFY_WEBHOOK_ALGO'];
		$hmac = @$_SERVER['HTTP_X_MOREIFY_WEBHOOK_HMAC'];

		$type = @$_SERVER['CONTENT_TYPE'];

		$endpoint = $this->getEndpointById($endpointId);

		$this->validate($endpoint, $url, $body, $algo, $hmac);

		$webhook = $this->getParser($type)->parse($body);

		$webhook->setEndpoint($endpoint);

		return $webhook;
	}

	/*
	 * @return string
	 * @throws WebhookException
	 */
	private function getSchema() {
		//TODO: check for trusted proxy, but this can be overwriten anyway!
		$schema = $_SERVER['REQUEST_SCHEME'];
		if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
			$schema = $_SERVER['HTTP_X_FORWARDED_PROTO'];
		}

		$schema = strtolower($schema);

		if (!in_array($schema, array('http', 'https'))) {
			throw new WebhookException('Invalid http schema detected ' . $schema);
		}

		return $schema;
	}

	/**
	 * @param WebhookEndpoint $endpoint
	 * @param string $url
	 * @param string $content
	 * @param string $algo
	 * @param string $hmac
	 * @return boolean
	 * @throws InvalidSignatureException
	 */
	private function validate(WebhookEndpoint $endpoint, $url, $content, $algo, $hmac) {

		if (!$endpoint->shallValidate()) {
			return false;
		}

		if (!strlen($hmac)) {
			throw new InvalidSignatureException("The provided hmac may not be empty!");
		}

		if (!$endpoint->getAlgo()) {
			$endpoint->setAlgo($algo);
		}

		$signature = $this->generateSignature($url . $content, $endpoint);

		if ($hmac !== $signature) {
			throw new InvalidSignatureException("Calculated($algo): $signature Expected($algo): $hmac");
		}

		return true;
	}

	/**
	 * @param string $endpointId
	 * @return WebhookEndpoint
	 * @throws EndpointNotFoundException
	 */
	private function getEndpointById($endpointId) {
		if (!isset($this->endpoints[$endpointId])) {
			throw new EndpointNotFoundException("endpoint with id " . $endpointId . "has not been configured");
		}
		return $this->endpoints[$endpointId];
	}

	/**
	 * @param string $data
	 * @param WebhookEndpoint $endpoint
	 * @return string
	 */
	private function generateSignature($data, WebhookEndpoint $endpoint) {
		return \hash_hmac($endpoint->getAlgo(), $data, $endpoint->getSecret());
	}

	/**
	 * @param string $contentType
	 * @return ParserInterface
	 * @throws ContentTypeNotSupportedException
	 */
	private function getParser($contentType) {
		if ($contentType == 'application/json') {
			return new JsonParser();
		}

		throw new ContentTypeNotSupportedException('The provided content type ' . $contentType . ' is not supported!');
	}

}