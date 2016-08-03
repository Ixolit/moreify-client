<?php

namespace Ixolit\Moreify;

use Ixolit\Moreify\Exception\InvalidLanguageException;
use Ixolit\Moreify\Exception\InvalidVerificationCodeException;
use Ixolit\Moreify\Exceptions\ApiCallFailedException;
use Ixolit\Moreify\Exceptions\InsufficientBalanceException;
use Ixolit\Moreify\Exceptions\InternalErrorException;
use Ixolit\Moreify\Exceptions\InvalidAuthenticationTokenException;
use Ixolit\Moreify\Exceptions\InvalidMessageException;
use Ixolit\Moreify\Exceptions\InvalidPhoneNumberException;
use Ixolit\Moreify\Exceptions\InvalidTagException;
use Ixolit\Moreify\Interfaces\HTTPClientAdapter;
use Ixolit\Moreify\Responses\SendSMSResponse;
use Ixolit\Moreify\Responses\VerificationCallResponse;

/**
 * @package Moreify
 */
class MoreifyClient {
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
	 * @param string            $project
	 * @param string            $password
	 * @param HTTPClientAdapter $httpClient
	 */
	public function __construct($project, $password, HTTPClientAdapter $httpClient) {
		$this->validateString($project);
		$this->validateString($password);

		$this->httpClient = $httpClient;
		$this->project = $project;
		$this->password = $password;
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
	 */
	private function sendRequest($method, $urlFragment, $payload) {
		$uri = $this->httpClient
			->createUri()
			->withScheme('https')
			->withHost('members.moreify.com')
			->withPath('/api/v1' . $urlFragment);
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
				case 1201:
					throw new InvalidMessageException($payload['message'], $responseData['errorCode']);
				case 1202:
				case 1203:
					throw new InvalidPhoneNumberException($payload['phonenumber'], $responseData['errorCode']);
				default:
					throw new ApiCallFailedException($responseData['errorMessage'], $responseData['errorCode']);
			}
		}
		return $responseData;
	}

	/**
	 * @param string $recipient Phone number in international format (00123456789)
	 * @param string $message   Text which will be sent to the recipient. If the message exceeds the maximum SMS text
	 *                          length of 160 characters, it will be split in several parts. Each part is sent
	 *                          separately. UTF-8 encoding is allowed but may also result in splitting the message
	 *                          into parts.
	 * @param string $tag       String which allows you to tag this specific message. E.g. an unique identifier from
	 *                          your side or some sort of grouping information, like a specific promotion id,
	 *                          name ... It gets returned in the response.
	 *
	 * @return SendSMSResponse
	 */
	public function sendSMS($recipient, $message, $tag = '') {
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
		$response = $this->sendRequest('POST', '/sendSms', $payload);

		return new SendSMSResponse(
			$response['success'],
			$response['message-identifier'],
			(isset($response['tag'])?$response['tag']:''));
	}

	public function verificationCall($recipient, $language, $verificationCode, $tag = '') {
		$this->validatePhoneNumber($recipient);
		$this->validateLanguage($language);
		$this->validateVerificationCode($verificationCode);

		$payload = array(
			'project' => $this->project,
			'password' => $this->password,
			'phonenumber' => (string)$recipient,
			'language' => (string)$language,
			'verifyCode' => (string)$verificationCode
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
		if (!\preg_match('/^00[0-9]+\Z/', $phoneNumber)) {
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
