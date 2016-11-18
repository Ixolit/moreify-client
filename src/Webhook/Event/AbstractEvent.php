<?php

namespace Ixolit\Moreify\Webhook\Event;

/**
 * Class AbstractEvent
 * @package Ixolit\Moreify\Webhook\Event
 */
abstract class AbstractEvent {

	const EVENT_MESSAGE_RECEIVE = 'message.receive';
	const EVENT_MESSAGE_DELIVERY = 'message.delivery';
	const EVENT_PING = 'ping';

	/**
	 * @param array $event
	 * @return $this
	 */
	public function setFromArray(array $event) {
		if (is_array($event) && !empty($event)) {
			foreach ($event as $key => $value) {
				$setter = "set" . ucfirst($this->camelize($key));
				if (method_exists($this, $setter)) {
					$this->{$setter}($value);
				}
			}
		}
		return $this;
	}

	/**
	 * @param string $word
	 * @return string mixed
	 */
	protected function camelize($word) {
	  return preg_replace('/(^|-)([a-z])/e', 'strtoupper("\\2")', $word);
	}

}