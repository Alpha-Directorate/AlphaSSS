<?php namespace AlphaSSS\Repositories;

use AlphaSSS\Helpers\Arr;

class Order {

	public static $table = 'orders';

	public static function create($user_id, \Bitpay\Invoice $invoice)
	{
		global $wpdb;

		$data = [
			'user_id'    => $user_id,
			'invoice_id' => $invoice->getId(),
			'url'        => $invoice->getUrl(),
			'status'     => $invoice->getStatus(),
			'btc_price'  => $invoice->getBtcPrice(),
			'btc_rate'   => $invoice->getRate(),
			'price'      => $invoice->getPrice()
		];

		$wpdb->insert( self::$table, $data, $format );
	}

	public static function update($order_id, Array $data)
	{

	}

	public static function find($order_id)
	{

	}

	public static function findOneBy(Array $args = [])
	{
		$args['is_single_row'] = TRUE;

		return self::findBy($args);
	}

	public static function findBy(Array $args = [])
	{
		global $wpdb;

		$query = sprintf('SELECT * FROM %s', self::$table);

		$where_args = [];

		if ($user_id = Arr::get($args, 'user_id')) {
			$where_args[] = sprintf('`user_id` = %d', $user_id);
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