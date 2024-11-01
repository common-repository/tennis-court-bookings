<?php
if ( ! defined( 'RNCBC_PLUGIN_DIR' ) ) {
	exit; // Exit if accessed directly
}
class RNCbcCourt {
	private static $table_name = 'rncbc_court';

	public static function find( $args = '' ) {

		global $wpdb, $table_prefix;

		$sql = 'select * from '.$table_prefix.self::$table_name.self::filter($args);

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

	public static function findAll($calendar_id) {
		global $wpdb, $table_prefix;
		return $wpdb->get_results('select * from '.$table_prefix.self::$table_name.' where calendar_id = '.intval($calendar_id).' and status = 1 order by id');
	}

	public static function findByPk($id) {
		global $wpdb, $table_prefix;

		$sql = 'select * from '.$table_prefix.self::$table_name.' where id = '.intval($id);

		return $wpdb->get_row($sql);
	}


	public static function count($args) {
		global $wpdb, $table_prefix;

		$sql = 'select count(id) count_id from '.$table_prefix.self::$table_name.self::filter($args);

		$result = $wpdb->get_row($sql);
		return $result->count_id;
	}

	public static function create($data) {
		global $wpdb, $table_prefix;
		return $wpdb->insert($table_prefix.self::$table_name, $data);
	}

	public static function delete($id) {
		global $wpdb, $table_prefix;
		return $wpdb->delete($table_prefix.self::$table_name, array('id' => $id));
	}

	public static function update($id, $data) {
		global $wpdb, $table_prefix;
		return $wpdb->update($table_prefix.self::$table_name, $data, array('id' => $id));
	}

	public static function filter($filter) {
		return '';
	}
}