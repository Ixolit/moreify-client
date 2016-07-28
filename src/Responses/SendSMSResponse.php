<?php

namespace Ixolit\Moreify\Responses;

/**
 * @package Moreify
 */
class SendSMSResponse {
	/**
	 * @var string
	 */
	private $messageIdentifier;
	/**
	 * @var string
	 */
	private $tag;
	/**
	 * @var string
	 */
	private $success;

	/**
	 * @param string $success
	 * @param string $messageIdentifier
	 * @param string $tag
	 */
	public function __construct($success, $messageIdentifier, $tag = '') {
		$this->messageIdentifier = $messageIdentifier;
		$this->tag = $tag;
		$this->success = $success;
	}

	/**
	 * @return string
	 */
	public function getSuccess() {
		return $this->success;
	}

	/**
	 * @return string
	 */
	public function getMessageIdentifier() {
		return $this->messageIdentifier;
	}

	/**
	 * @return string
	 */
	public function getTag() {
		return $this->tag;
	}
}
