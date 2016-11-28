<?php

require_once(__DIR__ . '/../vendor/autoload.php');

//create handler and assign at least one webhook endpoint
//the same script could handle multiple endpoints
$handler = new \Ixolit\Moreify\Webhook\WebhookHandler();

$shallValidateSignature = true;
$handler->addWebhookEndpoint(new \Ixolit\Moreify\Webhook\WebhookEndpoint('ENDPOINT-ID', 'ENDPOINT-SECRET', $shallValidateSignature));

$client = new \Ixolit\Moreify\MoreifyClient('PROJECT-IDENTIFIER', 'PROJECT-SECRET');
$client->setWebhookHandler($handler);

try {

	$webhook = $client->onWebhookReceive();

	//TODO: check if $webhook->getId() has already been processed, if yes acknowledge with 200 + $webhook->getId()

	//TODO: store webhook data for later processing.
	//NOTE: long running operations should NOT be performed directly on webhook receipt
	\file_put_contents('/tmp/webhooks.log', print_r($webhook, true), FILE_APPEND);

	//acknowledge receipt by answering with HTTP 200 + $webhook->getId()
	\http_response_code(200);
	echo $webhook->getId();
	die();

} catch (\Ixolit\Moreify\Webhook\Exception\WebhookException $e) {
	\http_response_code(400);
	echo $e->getMessage() . "\n";
}