<?php

namespace Ixolit\Moreify\Webhook\Parser;

use Ixolit\Moreify\Webhook\Webhook;
use Ixolit\Moreify\Webhook\Exception\ParseException;

/**
 * Class ParserInterface
 * @package Ixolit\Moreify\Webhook\Parser
 */
interface ParserInterface {

	/**
	 * @param string $body
	 * @return Webhook
	 * @throws ParseException
	 */
	public function parse($body);

}