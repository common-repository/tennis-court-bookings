<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) )
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

rncbc_import('model-calendar');

class RNCbcCalendarListTable extends WP_List_Table {
	protected $coaching_available = false;

	public function __construct() {
		parent::__construct( array(
			'singular'  => 'Calendar',     //singular name of the listed records
			'plural'    => 'Calendars',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );

		$this->coaching_available = rncbc_coaching_available();
	}

	public function column_default($item, $column_name){
		switch($column_name) {
			case 'operation':
				$buttons = '';
				$buttons .= '<a href="'.admin_url('admin.php?page=rncbc_calendars').'&action=update&id='.$item->id.'">Update</a>';
				$buttons .= ' - <a href="'.admin_url('admin.php?page=rncbc_calendars').'&action=reservations&id='.$item->id.'">Reservations</a>';

				if(!$this->coaching_available) {
					$buttons .= ' - <a href="'.admin_url('admin.php?page=rncbc_calendars').'&action=coaching&id='.$item->id.'" style="color: #777;">Coaching</a>';
				} else {
					$buttons .= ' - <a href="'.admin_url('admin.php?page=rncbc_calendars').'&action=coaching&id='.$item->id.'">Coaching</a>';
				}
				$buttons .= ' - <a href="'.admin_url('admin.php?page=rncbc_calendars').'&action=court&id='.$item->id.'">Courts</a>';

				$buttons .= ' - <a href="'.admin_url('admin.php?page=rncbc_calendars').'&action=delete&id='.$item->id.'" onClick="return confirm(\'Did you really want to delete this calendar?\')">Delete</a>';

				return $buttons;
				break;

			case 'shortcode':
				return '[rncbc_calendar id="'.$item->id.'"]';
				break;

			case 'create_time':
				return date('m/d/Y H:i', $item->$column_name);
				break;

			default:
				return $item->$column_name;
				break;
		}
	}

	public function prepare_items() {
		$per_page = 20;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$args = array(
			'per_page' => $per_page,
			'type' => $_GET['type'],
			'page' => $_GET['paged'],
			'orderby' => $_GET['orderby'],
			'order' => $_GET['order'],
		);

		$this->items = RNCbcCalendar::find( $args );

		$total_items = RNCbcCalendar::count( $args );
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page ) );
	}

	public function get_sortable_columns() {
		return array();
	}

	public function get_columns(){
		$columns = array(
			//'cb'        => '', //Render a checkbox instead of text
			'title'     => 'Title',
			'shortcode'     => 'Shortcode',
//			'booking_window_close'  => 'Booking window closed time',
//			'min_day'  => 'Minimum available date',
//			'max_day'    => 'Maximum available date',
			'create_time'  => 'Date',
			'operation'  => 'Operation',
		);
		return $columns;
	}

}