<?php

namespace SS6\ShopBundle\Model\Mail\Exception;

use Exception;
use SS6\ShopBundle\Component\Debug;

class SendMailFailedException extends Exception implements MailException {

	/**
	 * @var array
	 */
	private $failedRecipients;

	/**
	 * @param array $failedRecipients
	 * @param \Exception $previous
	 */
	public function __construct($failedRecipients, Exception $previous = null) {
		$this->failedRecipients = $failedRecipients;
		parent::__construct('Order mail was not send to ' . Debug::export($failedRecipients), 0, $previous);
	}

	/**
	 * @return array
	 */
	public function getFailedRecipients() {
		return $this->failedRecipients;
	}
}