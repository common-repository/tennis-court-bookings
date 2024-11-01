<?php
if ( ! defined( 'RNCBC_PLUGIN_DIR' ) ) {
	exit; // Exit if accessed directly
}
class RNCbcCalendarDate {
	private static $table_name = 'rncbc_calendar_date';

	public static function findAll( $calendar_id = '' ) {

		global $wpdb, $table_prefix;

		$sql = 'select * from '.$table_prefix.self::$table_name.' WHERE calendar_id = '. intval($calendar_id) .' order by day DESC ';

		return $wpdb->get_results($sql);
	}

	public static function find( $calendar_id, $day ) {

		global $wpdb, $table_prefix;

		$sql = 'select * from '.$table_prefix.self::$table_name.' WHERE calendar_id = '. intval($calendar_id) .' AND day = "'.$day.'" order by day DESC ';

		return $wpdb->get_row($sql);
	}

	public static function save($calendar_id, $date, $people) {
		foreach($date as $k=>$d) {
			$record = self::find($calendar_id, $k);

			if(!$record) {
				$tmp = array();
				foreach($d as $dv)
					$tmp[$dv] = $people;

				self::create(array(
					'calendar_id' => $calendar_id,
					'day' => $k,
					'time_slots' => json_encode($tmp),
				));
			} else {
				$time_slots = (array) json_decode($record->time_slots);
				$tmp = array();
				foreach($d as $dv) {
					$tmp[$dv] = $time_slots[$dv] ? $time_slots[$dv] + $people : $people;
				}

				self::update($record->id, array(
					'time_slots' => json_encode($tmp)
				));
			}
		}
	}

	public static function create($data) {
		global $wpdb, $table_prefix;
		return $wpdb->insert($table_prefix.self::$table_name, $data);
	}

	public static function update($id, $data) {
		global $wpdb, $table_prefix;
		return $wpdb->update($table_prefix.self::$table_name, $data, array('id' => $id));
	}
}