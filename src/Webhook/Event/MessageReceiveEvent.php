<?php

namespace Ixolit\Moreify\Webhook\Event;

/**
 * Class MessageReceiveEvent
 * @package Ixolit\Moreify\Webhook\Event
 */
class MessageReceiveEvent extends AbstractProjectEvent {

	//types
	const TYPE_SMS = 'sms';
	const TYPE_CALL = 'call';
	const TYPE_EMAIL = 'email';

	/**
	 * @var string
	 */
	private $messageIdentifier;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var string
	 */
	private $projectIdentifier;

	/**
	 * @var string
	 */
	private $receiver;

	/**
	 * @var string
	 */
	private $sender;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @var string
	 */
	private $county;

	/**
	 * @return string
	 */
	public function getMessageIdentifier() {
		return $this->messageIdentifier;
	}

	/**
	 * @param string $messageIdentifier
	 * @return $this
	 */
	public function setMessageIdentifier($messageIdentifier) {
		$this->messageIdentifier = $messageIdentifier;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType($type) {
		$this->type = $type;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getProjectIdentifier() {
		return $this->projectIdentifier;
	}

	/**
	 * @param string $projectIdentifier
	 * @return $this
	 */
	public function setProjectIdentifier($projectIdentifier) {
		$this->projectIdentifier = $projectIdentifier;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getReceiver() {
		return $this->receiver;
	}

	/**
	 * @param string $receiver
	 * @return $this
	 */
	public function setReceiver($receiver) {
		$this->receiver = $receiver;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSender() {
		return $this->sender;
	}

	/**
	 * @param string $sender
	 * @return $this
	 */
	public function setSender($sender) {
		$this->sender = $sender;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @param string $message
	 * @return $this
	 */
	public function setMessage($message) {
		$this->message = $message;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCounty() {
		return $this->county;
	}

	/**
	 * @param string $county
	 * @return $this
	 */
	public function setCounty($county) {
		$this->county = $county;

		return $this;
	}

}