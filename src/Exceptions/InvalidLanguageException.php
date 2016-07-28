<?php

namespace Ixolit\Moreify\Exception;

use Ixolit\Moreify\Exceptions\MoreifyException;

class InvalidLanguageException extends \InvalidArgumentException implements MoreifyException{
	/**
	 * @var string
	 */
	private $language;

	/**
	 * InvalidLanguageException constructor.
	 *
	 * @param string     $language
	 * @param int        $code
	 * @param \Exception $previous
	 */
	public function __construct($language, $code = 0, \Exception $previous = null) {
		parent::__construct('Unsupported language: ' . \var_export($language, true), $code, $previous);
		$this->language = $language;
	}

	/**
	 * @return string
	 */
	public function getLanguage() {
		return $this->language;
	}
}