<?php namespace AlphaSSS\Repositories;

if ( ! defined( 'ABSPATH' ) ) exit;

use \Carbon\Carbon;
use \AlphaSSS\Helpers\Arr;

class AccountingEvent {

	const SINGUP_EVENT             = 'singup';
	const SINGUP_BONUS_EVENT       = 'singup_bonus'; 
	const TALK_SESSION_EVENT       = 'talk_session';
	const GIFT_CARD_PURCHASE_EVENT = 'gift_card_puchase';

	public static $table = 'accounting_events';

	/**
	 * This methods creates a new accounting event
	 * 
	 * @param integer $user_id
	 * @param string $event_type The accounting event type
	 * @param float $income_credits The income amount in credits
	 * @param float withdrawal_credits The outcome amount in credits
	 * @param mixed $event_date The accounting event date
	 * 
	 * @return integer The id of created record
	 * 
	 * @throws InvalidArgumentException when user pass the invalid accounting event type
	 * @throws RuntimeException when user tries to withdraw more that he has
	 */
	public static function create($user_id, $event_type, $income_credits, $withdrawal_credits, $event_date = NULL)
	{
		global $wpdb;

		// Invalid accounting event type?
		if (! self::isValidEventType($event_type)) {
			throw new InvalidArgumentException('Wrong accounting event type was passed');
		}

		// Detect the user ballance
		$ballance = get_user_meta( $user_id, 'credit_balance', true );

		// If there the income credits increase ballance
		if ($income_credits > 0) {
			$ballance += $income_credits;
		}

		if ($withdrawal_credits > 0) {
			$ballance -= $withdrawal_credits;

			if ($ballance < 0) {
				throw new RuntimeException('Operation is not permitted');
			}
		}

		// No date was passed? Use current one.
		if (! $event_date) {
			$event_date = Carbon::now();
		}

		// Preparing the data for insert
		$data = [
			'user_id'            => (int) $user_id,
			'event_type'         => $event_type,
			'income_credits'     => $income_credits,
			'withdrawal_credits' => $withdrawal_credits,
			'ballance'           => $ballance,
			'event_date'         => $event_date
		];

		$accounting_event_id = $wpdb->insert( self::$table, $data );

		return $accounting_event_id;
	}

	/**
	 * This method checks is accounting event type is valid
	 * 
	 * @param string $event_type The accounting event type
	 * @return boolean
	 */
	public static function isValidEventType($event_type)
	{
		$accounting_events = [
			self::SINGUP_EVENT, 
			self::SINGUP_BONUS_EVENT, 
			self::TALK_SESSION_EVENT, 
			self::GIFT_CARD_PURCHASE_EVENT
		];

		return in_array($event_type, $accounting_events);
	}

	/**
	 * This methods adds a singup accounting event
	 * 
	 * @param integer $user_id
	 * @param mixed $event_date The singup accounting event creation date
	 * 
	 * @return void AccountingEvent::create
	 */
	public static function createSingUpEvent($user_id, $event_date = NULL)
	{
		return self::create($user_id, self::SINGUP_EVENT, 0, 0, $event_date);
	}

	public function findBy(Array $args = [])
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

		if ($result) {
			foreach ($result as &$record) {
				switch ($record['event_type']) {
					case self::SINGUP_EVENT:
						$record['description'] = __('Sing-up Event','alphasss');
					break;
				}
			}
		}

		return $result;
	}
}