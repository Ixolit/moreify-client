<?php

namespace Ixolit\Moreify\Webhook;

use Ixolit\Moreify\Webhook\Event\AbstractEvent;
use Ixolit\Moreify\Webhook\Event\Event;

/**
 * Class Webhook
 * Wrapper around a webhook event
 * @package Ixolit\Moreify\Webhook
 */
class Webhook {

	/**
	 * UUID
	 * @var string
	 */
	private $id;

	/**
	 * Project identifier
	 * @var string
	 */
	private $project;

	/**
	 * @var string
	 */
	private $eventName;

	/**
	 * @var AbstractEvent
	 */
	private $eventObject;

	/**
	 * UTC timestamp at event creation
	 * @var \DateTime
	 */
	private $created;

	/**
	 * How many times this webhook event was tried to be delivered
	 * @var int
	 */
	private $attempt;

	/**
	 * UTC timestamp at webhook delivery attempt
	 * @var \DateTime
	 */
	private $attemptedAt;

	/**
	 * @var WebhookEndpoint
	 */
	private $endpoint;

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
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getProject() {
		return $this->project;
	}

	/**
	 * @param string $project
	 * @return $this
	 */
	public function setProject($project) {
		$this->project = $project;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getEventName() {
		return $this->eventName;
	}

	/**
	 * @param string $eventName
	 * @return $this
	 */
	public function setEventName($eventName) {
		$this->eventName = $eventName;

		return $this;
	}

	/**
	 * @return AbstractEvent
	 */
	public function getEventObject() {
		return $this->eventObject;
	}

	/**
	 * @param AbstractEvent $event
	 * @return $this
	 */
	public function setEventObject(AbstractEvent $event) {
		$this->eventObject = $event;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * @param \DateTime $created
	 * @return $this
	 */
	public function setCreated(\DateTime $created) {
		$this->created = $created;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getAttempt() {
		return $this->attempt;
	}

	/**
	 * @param int $attempt
	 * @return $this
	 */
	public function setAttempt($attempt) {
		$this->attempt = $attempt;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getAttemptedAt() {
		return $this->attemptedAt;
	}

	/**
	 * @param \DateTime $attemptedAt
	 * @return $this
	 */
	public function setAttemptedAt(\DateTime $attemptedAt) {
		$this->attemptedAt = $attemptedAt;

		return $this;
	}

	/**
	 * @return WebhookEndpoint
	 */
	public function getEndpoint() {
		return $this->endpoint;
	}

	/**
	 * @param WebhookEndpoint $endpoint
	 * @return $this
	 */
	public function setEndpoint(WebhookEndpoint $endpoint) {
		$this->endpoint = $endpoint;

		return $this;
	}


}