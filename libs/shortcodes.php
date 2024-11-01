<?php
if ( ! defined( 'RNCBC_PLUGIN_DIR' ) ) {
	exit; // Exit if accessed directly
}

function rncbc_calendar_js($calendar) {
	$working_arr = array('su', 'mo', 'tu', 'we', 'th', 'fr', 'sa');
	$working_arr_key = array('su' => 0, 'mo' => 1, 'tu' => 2, 'we' => 3, 'th' => 4, 'fr' => 5, 'sa' => 6);
	$working_day = '';

	if($calendar->working_day) {
		foreach($working_arr as $k=>$v) {
			if(strpos($calendar->working_day, $v) !== false)
				$working_day .= $k.',';

		}
		$working_day = trim($working_day, ',');
	}
	$holiday = explode(',', $calendar->holiday);

	$min_day = ( $calendar->min_day ? $calendar->min_day : date('Y/m/d') );
	$max_day = preg_match("#^\+[0-9]+$#", $calendar->max_day) ? date("Y/m/d", strtotime("{$calendar->max_day} days")) : $calendar->max_day;
	$paypal_available = rncbc_paypal_available(true);

	$activity = array();
	$coaching_available = rncbc_coaching_available();
	if($coaching_available) {
		if(!class_exists('RNCbcCoaching'))
			require_once(RNCBC_COACHING_DIR . '/libs/model-coaching.php');

		$coaching = RNCbcCoaching::findAll(array(
			'is_deleted' => 0,
			'calendar_id' => $calendar->id,
			'start' =>  date('Y-m-d', strtotime($min_day)),
		));

		foreach($coaching as $c) {
			if(!$c->activities)
				continue;
			$activities = json_decode($c->activities, true);

			foreach($activities as $k=>$v) {
				$activity[$k] = $working_arr_key[$k];
			}
		}

	}

	$activity = implode(',', $activity);

    $member_price_enabled = get_option('rncbc_paypal_member_price_enabled') == 1 ? 'true' : 'false';
	return '<script type="text/javascript">
			if(typeof window.rncbcCalendar == "undefined") window.rncbcCalendar = {};
			window.rncbcCalendar.calendar_'. $calendar->id .' = {
				id:'. $calendar->id .',
				calendar: null,
				panel: null,
				title: "'. addslashes($calendar->title) .'",
				working_day: "'. $working_day .'",
				min_day: "'. $min_day .'",
				max_day: "'. $max_day .'",
				booking_window_close: '. intval(($calendar->booking_window_close * 60)) .',
				holiday: '. json_encode($holiday) .',
				activity: "'. $activity .'",
				booked: {},
				loading: false,
				step: '.($calendar->step_size ? $calendar->step_size : 1).',
				slot: '.($calendar->time_slot ? $calendar->time_slot : 30).',
				member_price_enabled: '.($member_price_enabled).',
				payment: '. ($paypal_available ? 'true' : 'false') .'
			};
			</script>';
}

function rncbc_calendar_form($calendar) {
	$paypal_available = rncbc_paypal_available(true);
	$currency = get_option('rncbc_paypal_currency', 'USD');
    $member_price_enabled = get_option('rncbc_paypal_member_price_enabled') == 1;

	$html = '';
	$html .= '<div class="rncbc_status">';
	$html .= '<label class="u"><span></span> '.__('Un-available', 'rncbc').'</label>';
	$html .= '<label class="a"><span></span> '.__('Available', 'rncbc').'</label>';
	$coaching_available = rncbc_coaching_available();
	if($coaching_available) {
		$html .= '<label class="c"><span></span> '.__('Activity', 'rncbc').'</label>';
	}

	if(rncbc_valid_license() !== TRUE) {
		$html .= '<label style="padding-left: 20px;"><a href="http://www.ezihosting.com/tennis-court-bookings/" target="_blank">Tennis Court Bookings by EZiHosting</a></label>';
	}

	$html .= '</div>';

	$html .= '<div id="rncbc_calendar_table_'.$calendar->id.'" class="rncbc_table_wrap">';
	$html .= '<div class="loading">'.__('Please pick an available day', 'rncbc').'</div>';
	//$html .= rncbc_court_table($calendar->id);
	$html .= '</div>';

	$html .= '<form id="rncbc_calendar_form_'.$calendar->id.'" class="rncbc_form" style="display:none">';
	$html .= '<input name="id" type="hidden" value="'. $calendar->id .'" />';
	$html .= '<input name="court_id" type="hidden" value="" />';
	$html .= '<input name="day" type="hidden" value="" />';
	$html .= '<div id="rncbc-calendar-timeslots-'. $calendar->id .'" class="rncbc-calendar-timeslots" data-id="'. $calendar->id .'"></div>';

	$html .= '<div class="rncbc_field court-name rncbc_field_inline">';
	$html .= '<label>'.__('Court', 'rncbc').': </label>';
	$html .= '<span></span>';
	$html .= '</div>';
	$html .= '<div class="rncbc_field  court-from rncbc_field_inline">';
	$html .= '<label>'.__('From', 'rncbc').': </label>';
	$html .= ' <span></span><input type="hidden" name="from" value="" />';
	$html .= '</div>';
	$html .= '<div class="rncbc_field rncbc_field_inline">';
	$html .= '<label>'.__('To', 'rncbc').': </label>';
	$html .= '<select name="to"></select>';
	$html .= '</div>';
	if($paypal_available) {
	    if($member_price_enabled) {
            $html .= '<div class="rncbc_field rncbc_field_inline">';
            $html .= '<label>'.__('Members', 'rncbc').': </label>';
            $html .= '<select name="is_member"><option value="0">No</option><option value="1">Yes</option></select>';
            $html .= '</div>';
        }

		$html .= '<div class="rncbc_field rncbc_field_inline">';
		$html .= '<label>'.__('Total', 'rncbc').': </label>';
		$html .= '<span class="total" style="color: #ff3635"></span> '.$currency;
		$html .= '</div>';
	}
	$html .= '<div class="rncbc_field">';
	$html .= '<label>'.__('Name', 'rncbc').' <em>*</em>: </label>';
	$html .= '<input type="text" name="name" value="" />';
	$html .= '</div>';
	$html .= '<div class="rncbc_field">';
	$html .= '<label>'.__('Email', 'rncbc').' <em>*</em>: </label>';
	$html .= '<input type="text" name="email" value="" />';
	$html .= '</div>';
	$html .= '<div class="rncbc_field">';
	$html .= '<label>'.__('Phone number', 'rncbc').' <em>*</em>: </label>';
	$html .= '<input type="text" name="phone" value="" />';
	$html .= '</div>';

	$html .= '<div class="rncbc_field">';
	$html .= '<label>'.__('Comment', 'rncbc').' </label>';
	$html .= '<textarea name="comments" row="3" style="width:100%"></textarea>';
	$html .= '</div>';
	$html .= '<div class="rncbc_field">';
	$button = $paypal_available ? __('Booking & Pay', 'rncbc') : __('Booking Now', 'rncbc');
	$html .= '<input type="button" value="'.$button.'" class="rncbc_submit" data-id="'. $calendar->id .'" />';
	$html .= '</div>';
	$html .= '</form>';

	if($coaching_available) {
		$html .= rncbc_coaching_form($calendar, $paypal_available, $currency);
	}

	return $html;
}

function rncbc_coaching_form($calendar, $paypal_available, $currency) {
	$html = '';
	$html .= '<form id="rncbc_coaching_form_'.$calendar->id.'" class="rncbc_form" style="display:none">';
	$html .= '<input name="id" type="hidden" value="" />';
	$html .= '<input name="day" type="hidden" value="" />';

	$html .= '<div class="rncbc_field coaching-title rncbc_field_inline">';
	$html .= '<label>'.__('Title', 'rncbc').': </label>';
	$html .= '<span></span>';
	$html .= '</div>';
	$html .= '<div class="rncbc_field coaching-name rncbc_field_inline">';
	$html .= '<label>'.__('Activity', 'rncbc').': </label>';
	$html .= '<span></span>';
	$html .= '</div>';

	$html .= '<div class="rncbc_field coaching-detail rncbc_field_inline">';
	$html .= '<label>'.__('Detail', 'rncbc').': </label>';
	$html .= '<div></div>';
	$html .= '</div>';

	$html .= '<div class="rncbc_field rncbc_field_inline">';
	$html .= '<label>'.__('Trainees', 'rncbc').': </label>';
	$html .= '<select name="people"></select>';
	$html .= '</div>';

	if($paypal_available) {
		$html .= '<div class="rncbc_field rncbc_field_inline">';
		$html .= '<label>'.__('Total', 'rncbc').': </label>';
		$html .= '<span class="total" style="color: #ff3635"></span> '.$currency;
		$html .= '</div>';
	}
	$html .= '<div class="rncbc_field">';
	$html .= '<label>'.__('Name', 'rncbc').' <em>*</em>: </label>';
	$html .= '<input type="text" name="name" value="" />';
	$html .= '</div>';
	$html .= '<div class="rncbc_field">';
	$html .= '<label>'.__('Email', 'rncbc').' <em>*</em>: </label>';
	$html .= '<input type="text" name="email" value="" />';
	$html .= '</div>';
	$html .= '<div class="rncbc_field">';
	$html .= '<label>'.__('Phone number', 'rncbc').' <em>*</em>: </label>';
	$html .= '<input type="text" name="phone" value="" />';
	$html .= '</div>';

	$html .= '<div class="rncbc_field">';
	$html .= '<label>'.__('Comment', 'rncbc').' </label>';
	$html .= '<textarea name="comments" row="3" style="width:100%"></textarea>';
	$html .= '</div>';
	$html .= '<div class="rncbc_field">';
	$button = $paypal_available ? __('Booking & Pay', 'rncbc') : __('Booking Now', 'rncbc');
	$html .= '<input type="button" value="'.$button.'" class="rncbc_coaching_submit" data-id="'. $calendar->id .'" />';
	$html .= '</div>';
	$html .= '</form>';

	return $html;
}


$rncbc_assets_loaded = false;
$rncbc_model_loaded = false;
function rncbc_sc_calendar($args) {
	$id = intval($args['id']);
	if(!$id)
		return '';

	$html = '';

	global $rncbc_model_loaded;
	if(!$rncbc_model_loaded) {
		rncbc_import('model-calendar');
		rncbc_import('model-calendar-date');
		rncbc_import('model-court');

		$html .= '<script>window.rncbcCalendarAjaxUrl = "'. admin_url('admin-ajax.php') .'";</script>';

		$rncbc_model_loaded = true;
	}


	$calendar = RNCbcCalendar::findByPk($id);
	if(!$calendar)
		return '';

	global $rncbc_asset_loaded;
	if(!$rncbc_asset_loaded) {
		rncbc_load_yui();
		wp_enqueue_script('rncbc-scrollto', plugins_url('assets/js/jquery.scrollTo.min.js', RNCBC_PLUGIN_NAME), array('jquery'), RNCBC_PLUGIN_VERSION, true);
		wp_enqueue_script('rncbc-calendar', plugins_url('assets/js/rncbc_calendar.js', RNCBC_PLUGIN_NAME), array('jquery', 'rncbc-scrollto'), RNCBC_PLUGIN_VERSION, true);


		$rncbc_asset_loaded = true;
	}


	$html .= '<div id="rncbc-calendar-content-'. $calendar->id .'">';
	$html .= '<div id="rncbc-calendar-'. $calendar->id .'" class="rncbc-calendar-wrap"></div>';
	$html .= rncbc_calendar_js($calendar);
	$html .= rncbc_calendar_form($calendar);
	$html .= '</div>';

	return $html;
}
add_shortcode("rncbc_calendar", "rncbc_sc_calendar");
