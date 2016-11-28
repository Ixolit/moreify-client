<?php

namespace Ixolit\Moreify\Webhook;

use Ixolit\Moreify\Webhook\Exception\AlgoNotSupportedException;

/**
 * Class WebhookEndpoint
 * @package Ixolit\Moreify\Webhook
 */
class WebhookEndpoint {

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $algo;

	/**
	 * @var string
	 */
	private $secret;

	/**
	 * whether or not to enable/disable validation of request signature check
	 * @var boolean
	 */
	private $validate = true;

	/**
	 * WebhookEndpoint constructor.
	 * @param string $id
	 * @param string $secret
	 * @param boolean $validate
	 */
	public function __construct($id, $secret, $validate=true) {
		$this->setId($id);
		$this->setSecret($secret);
		$this->setValidate($validate);
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param string $id
	 * @return $this
	 */
	public function setId($id) {

		if (!strlen($id)) {
			throw new \InvalidArgumentException('Please specify a non empty string as id. You find it within your dashboard in the webhook endpoint area!');
		}

		$this->id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAlgo() {
		return $this->algo;
	}

	/**
	 * @param string $algo
	 * @return WebhookEndpoint
	 * @throws AlgoNotSupportedException
	 */
	public function setAlgo($algo) {

		if (!$algo || !in_array($algo, hash_algos())) {
			throw new AlgoNotSupportedException(
				sprintf('Algo "%s" is not supported by your system, please install or configure a different one via the dashboard!', $algo)
			);
		}

		$this->algo = $algo;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSecret() {
		return $this->secret;
	}

	/**
	 * @param string $secret
	 * @return $this
	 */
	public function setSecret($secret) {

		if (!strlen($secret)) {
			throw new \InvalidArgumentException('Please specify a non empty string as secret. You find it within your dashboard in the webhook endpoint area!');
		}

		$this->secret = $secret;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function shallValidate() {
		return $this->validate;
	}

	/**
	 * @param boolean $validate
	 * @return $this
	 */
	public function setValidate($validate) {
		$this->validate = (bool) $validate;

		return $this;
	}

}