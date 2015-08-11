<?php namespace AlphaSSS\Repositories;

if ( ! defined( 'ABSPATH' ) ) exit;

use AlphaSSS\Helpers\Arr;
use \Bitpay\Invoice;

class Order {

	public static $table = 'orders';

	/**
	 * This method creates a new order and returns assoc array
	 * 
	 * @param integer $user_id User ID
	 * @param \Bitpay\Invoice $invoice BitPay invoice
	 * 
	 * @return array
	 */
	public static function create($user_id, Invoice $invoice)
	{
		global $wpdb;

		$data = [
			'user_id'      => $user_id,
			'invoice_id'   => $invoice->getId(),
			'url'          => $invoice->getUrl(),
			'status'       => $invoice->getStatus(),
			'btc_price'    => $invoice->getBtcPrice(),
			'btc_rate'     => $invoice->getRate(),
			'price'        => $invoice->getPrice(),
			'order_number' => self::generateOrderNumber($user_id)
		];

		$order_id = $wpdb->insert( self::$table, $data, $format );

		return self::find($order_id);
	}

	/**
	 * This method returns the unique order number
	 * 
	 * @see https://alphasss.atlassian.net/wiki/display/REQ/Use+Case+13+-+Purchase+Credits#UseCase13-PurchaseCredits-13.14
	 * @uses ord http://php.net/manual/en/function.ord.php
	 * @uses get_locale https://codex.wordpress.org/Function_Reference/get_locale
	 * 
	 * @param integer $user_id User ID
	 * 
	 * @return string
	 */
	public static function generateOrderNumber($user_id)
	{
		// Convert user locale from ASCII to decimal
		$locale_in_decimal = ord( substr( get_locale(), 3, 1 ) ) . ord( substr( get_locale(), 4, 1 ) );

		return sprintf( '%d-%s-%d', $user_id, date( 'ymd-his' ), $locale_in_decimal );
	}

	public static function update($order_id, Array $data)
	{

	}

	/**
	 * Method returns an order assoc array by order id
	 * 
	 * @param integer $order_id Order id
	 * 
	 * @return array
	 */
	public static function find($order_id)
	{
		return self::findOneBy(['order_ids' => [$order_id]]);
	}

	/**
	 * This methods returns one order fetched by arguments
	 * 
	 * @param array $args Search arguments
	 * 
	 * @return array
	 */
	public static function findOneBy(Array $args = [])
	{
		$args['is_single_row'] = TRUE;

		return self::findBy($args);
	}

	/**
	* This methods returns the collection of orders or singe order fetched by arguments
	* 
	* @param array $args Search arguments
	* 
	* @return array
	*/
	public static function findBy(Array $args = [])
	{
		global $wpdb;

		$query = sprintf('SELECT * FROM %s', self::$table);

		$where_args = [];

		if ($user_id = Arr::get($args, 'user_id')) {
			$where_args[] = sprintf('`user_id` = %d', $user_id);
		}

		if ($order_number = Arr::get($args, 'order_number')) {
			$where_args[] = sprintf('`order_number` = "%s"', $order_number);
		}

		if ($order_ids = Arr::get($args, 'order_ids')) {
			$where_args[] = sprintf('`id` IN (%s)', implode(',', $order_ids));
		}

		if (count($where_args) > 0) {
			$query .= ' WHERE ' . implode(' AND ', $where_args);
		}

		if ($order_by = Arr::get($args, 'order_by')) {
			$query .= sprintf(' ORDER BY `%s` %s', $order_by, Arr::get($args, 'sort', 'ASC'));
		}

		if (Arr::get($args, 'is_single_row')) {
			$result = $wpdb->get_row($query, ARRAY_A);
		} else {
			$result = $wpdb->get_results($query, ARRAY_A);
		}

		return $result;
	}

	public static function getLastUserOrder($user_id)
	{
		return self::findOneBy([
			'user_id'  => $user_id,
			'order_by' => 'id', 
			'sort'     => 'DESC'
		]);
	}
}