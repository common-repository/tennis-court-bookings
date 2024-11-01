<?php
if ( ! defined( 'RNCBC_PLUGIN_DIR' ) ) {
	exit; // Exit if accessed directly
}
class RNCbcCalendarReservation {
	private static $table_name = 'rncbc_reservation';
	public static $payment_status = array('Un-Paid', 'Paid', 'Refunded');

	public static function find( $args = '' ) {

		global $wpdb, $table_prefix;

		$sql = 'select a.*, b.name court_name from '.$table_prefix.self::$table_name." a  left join  {$table_prefix}rncbc_court b on b.id = a.court_id ".self::filter($args);
		$page = absint($args['paged']);
		if ( !$page )
			$page = 1;

		$args['per_page'] = $args['per_page'] ? $args['per_page'] : 20;
		if ( empty($q['offset']) ) {
			$pgstrt = ($page - 1) * $args['per_page'] . ', ';
		} else { // we're ignoring $page and using 'offset'
			$q['offset'] = absint($args['offset']);
			$pgstrt = $args['offset'] . ', ';
		}

		if($args['orderby']) {
			$sort_type = $args['order'] == 'desc' ? 'desc' : 'asc';
			$sql .= ' order by '.$args['orderby'].' '.$sort_type;
		} else {
			$sql .= ' order by  id DESC ';
		}

		$sql .= ' LIMIT ' . $pgstrt . $args['per_page'];

		return $wpdb->get_results($sql);
	}

	public static function findAll() {
		global $wpdb, $table_prefix;

		return $wpdb->get_results('select * from '.$table_prefix.self::$table_name);
	}

	public static function findAllByDay($day) {
		global $wpdb, $table_prefix;

		return $wpdb->get_results('select * from '.$table_prefix.self::$table_name.' where status = 1 and day = '.$day);
	}

	public static function findAllByDayCourt($day, $court_id) {
		global $wpdb, $table_prefix;

		return $wpdb->get_results('select * from '.$table_prefix.self::$table_name.' where status = 1 and day = '.$day.' AND court_id = '.$court_id);
	}

	public static function findByPk($id, $with_court = false) {
		global $wpdb, $table_prefix;

		if($with_court) {
			$sql = 'select a.*, b.name court_name from '.$table_prefix.self::$table_name." a  left join  {$table_prefix}rncbc_court b on b.id = a.court_id where a.id = ".intval($id);
		} else {
			$sql = 'select * from '.$table_prefix.self::$table_name.' where id = '.intval($id);

		}

		return $wpdb->get_row($sql);
	}


	public static function count($args) {
		global $wpdb, $table_prefix;

		$sql = 'select count(id) count_id from '.$table_prefix.self::$table_name.' a '.self::filter($args);

		$result = $wpdb->get_row($sql);
		return $result->count_id;
	}

	public static function create($data) {
		global $wpdb, $table_prefix;
//		return
		if($wpdb->insert($table_prefix.self::$table_name, $data)) {
			return $wpdb->insert_id;
		} else {
			return false;
		}
	}

	public static function delete($id) {
		global $wpdb, $table_prefix;
		return $wpdb->delete($table_prefix.self::$table_name, array('id' => $id));
	}

	public static function update($id, $data) {
		global $wpdb, $table_prefix;
		return $wpdb->update($table_prefix.self::$table_name, $data, array('id' => $id));
	}

	public static function filter($args) {
		$calendar_id = intval($args['calendar_id']);
		return  ' WHERE a.calendar_id = '.$calendar_id.' ';
	}

    public static function clearUnpaidReservations() {
        global $wpdb, $table_prefix;
        $table = $table_prefix.self::$table_name;
        $end = time() - 600;//ten minutes later
        $start = strtotime(date('Y-m-d 00:00:00'));
        $sql = "UPDATE {$table} SET status = 0 WHERE payment_status = 0 and create_time >= {$start} and create_time < {$end} and status = 1";
        //echo $sql;
        $wpdb->get_results($sql);
    }
}