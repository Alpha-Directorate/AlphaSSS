<?php namespace Codeception\Module;

use Codeception\Module\Mailtrap;


class MailtrapHelper extends Mailtrap {

	/**
	 * Look for the activation link in the most recent email (HTML).
	 *
	 * @param $expected
	 *
	 * @return mixed
	 */
	public function seeActivationLink()
	{
		$message   = $this->fetchLastMessage();
		$html_body = $message['html_body'];

		return (boolean) preg_match('/<a href=".*action=confirm&eckey=([A-z0-9]+)"/', $html_body);
	}


	/**
	 * Returns activation link
	 *
	 * @param $expected
	 *
	 * @return mixed
	 */
	public function getActivationLink()
	{
		if ($this->seeActivationLink()) {
			$message   = $this->fetchLastMessage();
			$html_body = $message['html_body'];

			preg_match('/<a href="(.*action=confirm&eckey=[A-z0-9]+)"/', $html_body, $result);
			
			return $result[1];
		}

		return false;
	}

	/**
     * Click to the activation link in the most recent email (HTML).
     *
     * @param $expected
     *
     * @return mixed
     */
	public function clickActivationLink()
	{
		$message = $this->fetchLastMessage();
	}

}