<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) )
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

rncbc_import('model-calendar-reservation');

class RNCbcCalendarReservationListTable extends WP_List_Table {
	private $paypal_available = false;

	public function __construct() {
		parent::__construct( array(
			'singular'  => 'Reservation',     //singular name of the listed records
			'plural'    => 'Reservations',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );


		$this->paypal_available = rncbc_paypal_available();
	}

	public function column_default($item, $column_name){
		switch($column_name) {
			case 'reservation' :
				$res = json_decode($item->$column_name);
				$is_member = $res->is_member ? '<br />[Member Price]' : '';

				$html = '<p>' .date('m/d/Y', strtotime($item->day)). '<br />'.$res->from.' to '. $res->to . '<br />'.$item->court_name.$is_member.'</p>';

				return $html;
				break;

			case 'email':
				return $item->email.'<br />'.$item->phone;
				break;

			case 'comment':
				return $item->comments;
				break;

			case 'create_time':
				return date('m/d/Y H:i', $item->$column_name);
				break;

			case 'payment_status':
				if($this->paypal_available) {
					if($item->payment_status == 1) {
						$style = 'style="color:#0eb310"';
					} else {
						$style = 'style="color:#ff3635"';
					}
					return '<b '.$style.'>'.($item->payment_total ? $item->payment_total.'/' : '').RNCbcCalendarReservation::$payment_status[$item->payment_status].'</b>';

				} else {
					return 'Un-Paid';
				}
				break;

			case 'operation':
				$buttons = '';
				$buttons .= '<a href="'.admin_url('admin.php?page=rncbc_calendars').'&action=reservations_delete&calendar_id='.$item->calendar_id.'&id='.$item->id.'" onClick="return confirm(\'Did you really want to delete this reservation?\')">Delete</a>';

				return $buttons;
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
			'calendar_id' => intval($_GET['id']),
			'paged' => $_GET['paged'],
			'orderby' => $_GET['orderby'],
			'order' => $_GET['order'],
		);

		$this->items = RNCbcCalendarReservation::find( $args );

		$total_items = RNCbcCalendarReservation::count( $args );
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
			'name'     => 'Name',
			'email'  => 'Email & Phone',
//			'phone'  => 'Phone',
//			'court_name'  => 'Court',
			'reservation'  => 'Reservations',
			'comment'    => 'Comments',
			'payment_status'    => 'Payment',
			'create_time'  => 'Date',
			'operation'  => 'Operation',
		);
		return $columns;
	}

}