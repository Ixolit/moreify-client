<?php

namespace Ixolit\Moreify\Webhook;

use Ixolit\Moreify\Webhook\Exception\WebhookException;

/**
 * Interface WebhookHandlerInterface
 * @package Ixolit\Moreify\Webhook
 */
interface WebhookHandlerInterface {

	/**
	 * Retrieves request data, validates and returns a webhook wrapper object which holds
	 * the actual event with its data
	 * @return Webhook
	 * @throws WebhookException
	 */
	public function handle();

}