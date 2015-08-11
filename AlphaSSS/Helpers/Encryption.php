<?php namespace AlphaSSS\Helpers;

if ( ! defined( 'ABSPATH' ) ) exit;

use AlphaSSS\Helpers\Arr;

/**
 * @author Fractal Overflow <fractal-overflow@alphadirectorate.com>
 * @version 0.1
 * @copyright alphasss.com 2015
 */
class Encryption {

	/**
	 * @var string $cypher
	 */
	private $cypher;

	/**
	 * @var string $mode
	 */
	private $mode;

	/**
	 * @var string $key
	 */
	private $key;

	/**
	 * @var string $iv
	 */
	private $iv;

	/**
	 * @param array $config
	 */
	public function __construct(Array $config = array())
	{
		// Detect crypt algoritm
		$this->cypher = Arr::get($config, 'cypher', 'rijndael-128'); 

		// Detect crypt algoritm mode
		$this->mode = Arr::get($config, 'mode', 'cbc');

		// Detect crypt password key
		$this->key = Arr::get($config, 'key', 'Startrack');

		// Detect crypt iv
		$this->iv = Arr::get($config, 'iv', 'f9e8d7c6b5a43210');
	}

	/**
	 * Method sets encryption cyper
	 * 
	 * @param string $cyper Encryption cyper
	 * @return AlphaSSS\Helpers\Encription
	 */
	public function setCypher($cypher)
	{
		$this->cypher = $cypher;

		return $this;
	}

	/**
	 * Method returns current cyper
	 * 
	 * @return sting
	 */
	public function getCypher()
	{
		return $this->cypher;
	}

	/**
	 * Method sets encryption key
	 * 
	 * @param string $key Encryption key
	 * @return AlphaSSS\Helpers\Encription
	 */
	public function setKey($key)
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * Method returns current encription key
	 * 
	 * @return sting
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	* Method returns decrypted string
	* 
	* @param string $text Text that need to be decrypted
	* @return string
	*/
	public function decode($text)
	{
		$td = mcrypt_module_open($this->cypher, '', $this->mode, $this->iv);

		mcrypt_generic_init($td, $this->key, $this->iv);

		$decrypted = mdecrypt_generic($td, $this->hex2bin($text));

		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		//--

		return utf8_encode(trim($decrypted));
	}

	/**
	* Method returns encrypted string
	* 
	* @param string $text Text that need to be decrypted
	* @return string
	*/
	public function encode($text)
	{
		$td = mcrypt_module_open($this->cypher, '', $this->mode, $this->iv);
		mcrypt_generic_init($td, $this->key, $this->iv);
		$encrypted = mcrypt_generic($td, $text);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
			
		return bin2hex($encrypted);
	}

	/**
	 * Convert hex string to binary
	 * 
	 * @param string $hexdata
	 * @return string
	 */
	protected function hex2bin($hexdata) 
	{
		$bindata = '';

		for ($i = 0; $i < strlen($hexdata); $i += 2) {
			$bindata .= chr(hexdec(substr($hexdata, $i, 2)));
		}

		return $bindata;
	}
}