<?php

namespace Ixolit\Moreify\Webhook\Parser;

use Ixolit\Moreify\Webhook\Event\AbstractEvent;
use Ixolit\Moreify\Webhook\Event\MessageDeliveryEvent;
use Ixolit\Moreify\Webhook\Event\MessageReceiveEvent;
use Ixolit\Moreify\Webhook\Event\PingEvent;
use Ixolit\Moreify\Webhook\Webhook;
use Ixolit\Moreify\Webhook\Exception\ParseExceptio;

/**
 * Class JsonParser
 * @package Ixolit\Moreify\Webhook\Parser
 */
class JsonParser implements ParserInterface {

	/**
	 * @param string $body
	 * @return Webhook
	 * @throws ParseException
	 */
	public function parse($body) {

		$event = \json_decode($body, true);

		$webhook = new Webhook();
		$webhook->setId($event['id']);
		if (!empty($event['project'])) {
			$webhook->setProject($event['project']);
		}
		$webhook->setEventName($event['event']);
		$webhook->setAttempt($event['attempt']);

		$created = new \DateTime($event['created'], new \DateTimeZone('UTC'));
		$webhook->setCreated($created);

		$attemptedAt = new \DateTime($event['attempted_at'], new \DateTimeZone('UTC'));
		$webhook->setAttemptedAt($attemptedAt);

		$ev = null;
		switch($event['event']) {
			case AbstractEvent::EVENT_MESSAGE_RECEIVE:
				$ev = new MessageReceiveEvent();
				break;
			case AbstractEvent::EVENT_MESSAGE_DELIVERY:
				$ev = new MessageDeliveryEvent();
				break;
			case AbstractEvent::EVENT_PING:
				$ev = new PingEvent();
				break;
			default:
				throw new ParseException('Unable to parse provided event ' . $event['event']);
		}

		if (!empty($event['data']) && is_array($event['data'])) {
			$ev->setFromArray($event['data']);
		}

		if ($ev instanceof AbstractEvent) {
			$webhook->setEventObject($ev);
		}

		return $webhook;

	}


}