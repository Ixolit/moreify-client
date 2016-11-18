<?php

namespace Ixolit\Moreify;

use Ixolit\Moreify\Exception\InvalidLanguageException;
use Ixolit\Moreify\Exception\InvalidVerificationCodeException;
use Ixolit\Moreify\Exceptions\ApiCallFailedException;
use Ixolit\Moreify\Exceptions\GroupsNotSupportedException;
use Ixolit\Moreify\Exceptions\InsufficientBalanceException;
use Ixolit\Moreify\Exceptions\InternalErrorException;
use Ixolit\Moreify\Exceptions\InvalidAuthenticationTokenException;
use Ixolit\Moreify\Exceptions\InvalidActionException;
use Ixolit\Moreify\Exceptions\InvalidMessageException;
use Ixolit\Moreify\Exceptions\InvalidMessageTypeException;
use Ixolit\Moreify\Exceptions\InvalidPhoneNumberException;
use Ixolit\Moreify\Exceptions\InvalidTagException;
use Ixolit\Moreify\Exceptions\InvalidUserException;
use Ixolit\Moreify\Exceptions\UrlsNotAllowedException;
use Ixolit\Moreify\Exceptions\VerifyCodeMismatchException;
use Ixolit\Moreify\Interfaces\HTTPClientAdapter;
use Ixolit\Moreify\Responses\ConfirmResponse;
use Ixolit\Moreify\Responses\SendSMSResponse;
use Ixolit\Moreify\Responses\VerificationCallResponse;
use Ixolit\Moreify\Webhook\Webhook;
use Ixolit\Moreify\Webhook\WebhookHandlerInterface;
use Ixolit\Moreify\Webhook\Exception\HandlerConfigurationException;

/**
 * @package Moreify
 */
class MoreifyClient {

	/**
	 * @var string
	 */
	public $uriScheme = 'https';

	/**
	 * @var string
	 */
	public $uriHost = 'mapi.moreify.com';

	/**
	 * @var string
	 */
	public $uriPath = '/api/v1';

	/**
	 * @var HTTPClientAdapter
	 */
	private $httpClient;
	/**
	 * @var string
	 */
	private $project;
	/**
	 * @var string
	 */
	private $password;

	/**
	 * @var WebhookHandlerInterface
	 */
	private $webhookHandler;

	/**
	 * @param string            $project
	 * @param string            $password
	 * @param HTTPClientAdapter $httpClient
	 * @param WebhookHandlerInterface	$webhookHandler
	 */
	public function __construct($project, $password, HTTPClientAdapter $httpClient=null, WebhookHandlerInterface $webhookHandler=null) {
		$this->validateString($project);
		$this->validateString($password);

		$this->httpClient = $httpClient;
		$this->project = $project;
		$this->password = $password;

		$this->webhookHandler = $webhookHandler;
	}

	/**
	 * @param string $method
	 * @param string $urlFragment
	 * @param array  $payload
	 *
	 * @return array
	 *
	 * @throws ApiCallFailedException
	 * @throws InsufficientBalanceException
	 * @throws InternalErrorException
	 * @throws UrlsNotAllowedException
	 */
	private function sendRequest($method, $urlFragment, $payload) {
		$uri = $this->httpClient
			->createUri()
			->withScheme($this->uriScheme)
			->withHost($this->uriHost)
			->withPath($this->uriPath . $urlFragment);
		$request = $this->httpClient->createRequest()
			->withUri($uri)
			->withMethod($method)
			->withHeader('Content-Type', 'application/json')
			->withBody($this->httpClient->createStringStream(\json_encode($payload)));
		try {
			$response = $this->httpClient->send($request);
		} catch (\Exception $e) {
			throw new ApiCallFailedException($e->getMessage(), $e->getCode(), $e);
		}

		$responseData = \json_decode($response->getBody(), true);
		if (\json_last_error()) {
			throw new ApiCallFailedException('Invalid JSON response from Moreify API: ' . $response->getBody());
		}

		if ($responseData['success'] == false) {

			if (!array_key_exists('errorCode', $responseData)) {
				throw new ApiCallFailedException('Unknown error');
			}

			switch ($responseData['errorCode']) {
				case 1000:
				case 1001:
				case 1002:
					throw new InternalErrorException($responseData['errorMessage'], $responseData['errorCode']);
				case 1100:
					throw new InsufficientBalanceException($responseData['errorMessage'], $responseData['errorCode']);
				case 1101:
					throw new InvalidAuthenticationTokenException(
						$responseData['errorMessage'],
						$responseData['errorCode']);
				case 1102:
					throw new InvalidUserException(
						$responseData['errorMessage'],
						$responseData['errorCode']);
				case 1103:
					throw new InvalidActionException(
						$responseData['errorMessage'],
						$responseData['errorCode']);
				case 1104:
					throw new GroupsNotSupportedException(
						$responseData['errorMessage'],
						$responseData['errorCode']);
				case 1105:
					throw new InvalidMessageTypeException(
						$responseData['errorMessage'],
						$responseData['errorCode']);
				case 1201:
					throw new InvalidMessageException($payload['message'], $responseData['errorCode']);
				case 1202:
				case 1203:
					throw new InvalidPhoneNumberException($payload['phonenumber'], $responseData['errorCode']);
				case 1204:
					throw new InvalidLanguageException($payload['language'], $responseData['errorCode']);
				case 1205:
					throw new InvalidVerificationCodeException($payload['verifycode'], $responseData['errorCode']);
				case 1209:
					throw new UrlsNotAllowedException($payload['message'], $responseData['errorDetails'], $responseData['errorCode']);
				case 1210:
					throw new VerifyCodeMismatchException($payload['verifycode'], $responseData['errorCode']);
				default:
					throw new ApiCallFailedException($responseData['errorMessage'], $responseData['errorCode']);
			}
		}
		return $responseData;
	}

	/**
	 * Send given message and optional verification code
	 *
	 * @param string $recipient Phone number in international format (00123456789)
	 * @param string $message   Text which will be sent to the recipient. If the message exceeds the maximum SMS text
	 *                          length of 160 characters, it will be split in several parts. Each part is sent
	 *                          separately. UTF-8 encoding is allowed but may also result in splitting the message
	 *                          into parts.
	 * @param string $tag       String which allows you to tag this specific message. E.g. an unique identifier from
	 *                          your side or some sort of grouping information, like a specific promotion id,
	 *                          name ... It gets returned in the response.
	 * @param string $verificationCode
	 * 				 The 4 digit code used for verification if this is a 2FA sms
	 *
	 *
	 * @return SendSMSResponse
	 */
	public function sendSMS($recipient, $message, $tag = '', $verificationCode=null) {
		$this->validatePhoneNumber($recipient);
		$this->validateMessage($message);
		$this->validateTag($tag);

		$payload = array(
			'project' => $this->project,
			'password' => $this->password,
			'phonenumber' => (string)$recipient,
			'message' => (string)$message,
		);

		if ($tag) {
			$payload['tag'] = $tag;
		}

		if ($verificationCode) {
			$this->validateVerificationCode($verificationCode);
			$payload['verifycode'] = $verificationCode;
		}
		$response = $this->sendRequest('POST', '/sendSms', $payload);

		return new SendSMSResponse(
			$response['success'],
			$response['message-identifier'],
			(isset($response['tag'])?$response['tag']:''));
	}

	/**
	 * Send call with given verification code
	 *
	 * @param string $recipient Phone number in international format (00123456789)
	 * @param string $language
	 * @param string $verificationCode
	 * 				            The 4 digit code used for verification if this is a 2FA sms
	 * @param string $tag       String which allows you to tag this specific message. E.g. an unique identifier from
	 *                          your side or some sort of grouping information, like a specific promotion id,
	 *                          name ... It gets returned in the response.
	 *
	 *
	 * @return VerificationCallResponse
	 */
	public function verificationCall($recipient, $language, $verificationCode, $tag = '') {
		$this->validatePhoneNumber($recipient);
		$this->validateLanguage($language);
		$this->validateVerificationCode($verificationCode);

		$payload = array(
			'project' => $this->project,
			'password' => $this->password,
			'phonenumber' => (string)$recipient,
			'language' => (string)$language,
			'verifycode' => (string)$verificationCode
		);
		if ($tag) {
			$payload['tag'] = $tag;
		}

		$response = $this->sendRequest('POST', '/sendCall', $payload);

		return new VerificationCallResponse(
			$response['success'],
			$response['message-identifier'],
			(isset($response['tag'])?$response['tag']:'')
		);
	}

	/**
	 * Confirm the given message
	 *
	 * @param string $messageIdentifier message to confirm, returned by sendSMS
	 *
	 * @return ConfirmResponse
	 */
	public function confirm($messageIdentifier) {

		$payload = array(
			'project' => $this->project,
			'password' => $this->password,
			'message-identifier' => $messageIdentifier
		);

		$response = $this->sendRequest('POST', '/confirm', $payload);

		return new ConfirmResponse($response['success']);
	}

	/**
	 * Initiate Two Factor Authentication by sending a random code to the recipient
	 *
	 * @param string $recipient Phone number in international format (00123456789)
	 * @param string $language  Needed to choose message text
	 * @param string $type      Type of message (SMS, call)
	 * @param string $tag       String which allows you to tag this specific message. E.g. an unique identifier from
	 *                          your side or some sort of grouping information, like a specific promotion id,
	 *                          name ... It gets returned in the response.
	 *
	 * @return SendSMSResponse
	 */
	public function send2fa($recipient, $language, $type = 'sms', $tag = '') {
		$this->validatePhoneNumber($recipient);
		$this->validateTag($tag);

		$payload = array(
			'project' => $this->project,
			'password' => $this->password,
			'phonenumber' => (string) $recipient,
			'language' => (string) $language,
			'type' => (string) $type,
		);

		if ($tag) {
			$payload['tag'] = (string) $tag;
		}

		$response = $this->sendRequest('POST', '/send2faCode', $payload);

		return new SendSMSResponse(
			$response['success'],
			$response['message-identifier'],
			(isset($response['tag'])?$response['tag']:''));
	}

	/**
	 * Complete Two Factor Authentication by passing the random code sent to the recipient previously
	 *
	 * @param string $messageIdentifier message to confirm, returned by send2fa
	 * @param string $verifyCode code sent to and entered by recipient
	 *
	 * @return ConfirmResponse
	 */
	public function verify2fa($messageIdentifier, $verifyCode) {

		$payload = array(
			'project' => $this->project,
			'password' => $this->password,
			'message-identifier' => (string) $messageIdentifier,
			'verifycode' => (string) $verifyCode,
		);

		$response = $this->sendRequest('POST', '/verify2faCode', $payload);

		return new ConfirmResponse($response['success']);
	}

	/**
	 * @param WebhookHandlerInterface $webhookHandler
	 * @return $this
	 */
	public function setWebhookHandler(WebhookHandlerInterface $webhookHandler) {
		$this->webhookHandler = $webhookHandler;

		return $this;
	}

	/**
	 * Handle an incoming webhook request, validate and return a webhook object which holds the actual event object.
	 * In order to acknowledge the receipt, answer with 200 OK and return the $webhook->getId() as body
	 * The webhook->getId() is a unique identifier which can be checked on your side in order to prevent further processing of already stored/processed webhook event
	 * @return Webhook
	 * @throws HandlerConfigurationException
	 *
	 */
	public function onWebhookReceive() {

		if ($this->webhookHandler === null) {
			throw new HandlerConfigurationException('WebhookHandler implementing WebhookHandlerInterface has not been set');
		}

		return $this->webhookHandler->handle();
	}

	//region Validation
	/**
	 * Validates an argument to be a string.
	 *
	 * @param string $string
	 *
	 * @throws \InvalidArgumentException
	 */
	private function validateString($string) {
		if (\is_int($string) || \is_bool($string) || \is_float($string) || \is_string($string)) {
			return;
		}
		if (\is_object($string) && \method_exists($string, '__toString')) {
			return;
		}

		$type = \gettype($string);
		if ($type == 'object') {
			$type = \get_class($string);
		}
		$message = 'Invalid object type: ' . $type;
		$message .= ' expected string';

		throw new \InvalidArgumentException($message);
	}

	/**
	 * @param string $phoneNumber
	 *
	 * @throws InvalidPhoneNumberException
	 */
	private function validatePhoneNumber($phoneNumber) {
		try {
			$this->validateString($phoneNumber);
		} catch (\InvalidArgumentException $e) {
			throw new InvalidPhoneNumberException($phoneNumber, $e);
		}
		if (!\preg_match('/^(?:\+|00)[0-9]+\Z/', $phoneNumber)) {
			throw new InvalidPhoneNumberException($phoneNumber);
		}
	}

	/**
	 * @param string $message
	 *
	 * @throws InvalidMessageException
	 */
	private function validateMessage($message) {
		try {
			$this->validateString($message);
		} catch (\InvalidArgumentException $e) {
			throw new InvalidMessageException($message, 0, $e);
		}

		if (\strlen($message) == 0) {
			throw new InvalidMessageException($message, 1201);
		}
	}

	/**
	 * @param string $tag
	 *
	 * @throws InvalidTagException
	 */
	private function validateTag($tag) {
		try {
			$this->validateString($tag);
		} catch (\InvalidArgumentException $e) {
			throw new InvalidTagException($tag, $e);
		}
		if (\strlen($tag) > 50) {
			throw new InvalidTagException($tag);
		}
	}

	/**
	 * @param string $language
	 *
	 * @throws InvalidLanguageException
	 */
	private function validateLanguage($language) {
		try {
			$this->validateString($language);
		} catch (\Exception $e) {
			throw new InvalidLanguageException($language, $e->getCode(), $e);
		}
		if (!\in_array($language, array('en','de','fr','tr','nl','es','hu'))) {
			throw new InvalidLanguageException($language);
		}
	}

	/**
	 * @param string $verificationCode
	 *
	 * @throws InvalidVerificationCodeException
	 */
	private function validateVerificationCode($verificationCode) {
		try {
			$this->validateString($verificationCode);
		} catch (\Exception $e) {
			throw new InvalidVerificationCodeException($verificationCode, $e->getCode(), $e);
		}
		if (!\preg_match('/^[0-9]{4}\Z/', $verificationCode)) {
			throw new InvalidVerificationCodeException($verificationCode, 1205);
		}
	}
	//endregion Validation
}
