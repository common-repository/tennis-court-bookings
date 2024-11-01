<?php
if ( ! defined( 'RNCBC_PLUGIN_DIR' ) ) {
    exit; // Exit if accessed directly
}

function rncbc_ajax_booking(){
    $id = intval($_POST['id']);
    if(!$id)
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Record not found'
        ));

    $court_id = intval($_POST['court_id']);
    if(!$court_id)
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Record not found'
        ));

    $day = strtotime($_POST['day']);
    if(!$day)
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Record not found'
        ));

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Invalid email address.'
        ));


    rncbc_import('model-calendar');
    rncbc_import('model-court');
    rncbc_import('model-calendar-reservation');

    $calendar = RNCbcCalendar::findByPk($id);
    if(!$calendar)
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Record not found'
        ));

    $court = RNCbcCourt::findByPk($court_id);
    if(!$court || $court->calendar_id != $id || $court->status != 1)
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Court not found'
        ));

    if(!$_POST['name'] || !$_POST['email'] || !$_POST['phone'])
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Invalid params'
        ));

    //valid min day
    $min_day = strtotime($calendar->min_day) ? date('Ymd', strtotime($calendar->min_day)) : date('Ymd');
    if($_POST['day'] < $min_day || $_POST['day'] < date('Ymd', $day))
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Invalid day'
        ));

    //valid max day
    if($calendar->max_day) {
        $max_day = preg_match("#^\+[0-9]+$#", $calendar->max_day) ? date("Ymd", strtotime("{$calendar->max_day} days")) : date("Ymd", strtotime($calendar->max_day));
        if($_POST['day'] > $max_day)
            rncbc_show_json(array(
                'error' => true,
                'msg' => 'Invalid day'
            ));
    }


    //valid working day
    $working_arr = array('su', 'mo', 'tu', 'we', 'th', 'fr', 'sa');
    $working_day = array();
    if($calendar->working_day) {
        foreach($working_arr as $k=>$v) {
            if(strpos($calendar->working_day, $v) !== false)
                $working_day[] = $k;
        }
    }

    //valid holiday
    $holiday = (Array) explode(',', $calendar->holiday);
    foreach($holiday as $h) {
        if($h == date('Y-m-d', $day))
            rncbc_show_json(array(
                'error' => true,
                'msg' => __('Sorry, This day is closed.', 'rncbc')
            ));
    }



    $post_from = str_replace(':', '', $_POST['from']);
    if($post_from < str_replace(':', '', $calendar->from))
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Invalid from date'
        ));

    $post_to = str_replace(':', '', $_POST['to']);
    if($post_to > str_replace(':', '', $calendar->to))
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Invalid to date'
        ));

    if($post_to <= $post_from)
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Invalid booking time'
        ));


    //valid start time
    $now = rncbc_time();
    $start = false;
    if(date('Y-m-d', $day) == date('Y-m-d', $now)) {
        $close_time = $now + $calendar->booking_window_close * 60;
        if(date('i', $close_time) < 30) {
            $start = date('H', $close_time).':30';
        } else {
            $start = (date('H', $close_time)).':00';
        }
    }

    if($start && $post_from < str_replace(':', '', $start))
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Invalid from date'
        ));

//	if($)

//    $arr_from = explode(':', $_POST['from']);
//    $arr_end = explode(':', $_POST['to']);//06-

    $total_slot = ceil(rncbc_diff_minutes($_POST['from'], $_POST['to'])/($calendar->time_slot ? $calendar->time_slot : 30));

    rncbc_valid_coaching4booking($court_id, $day, $post_from, $post_to);


    //valid reservation
    $days = RNCbcCalendarReservation::findAllByDayCourt(date('Ymd', $day), $court_id);

    foreach($days as $d) {
        $re = json_decode($d->reservation);
        $f = str_replace(':', '', $re->from);
        $t = str_replace(':', '', $re->to);
        if($f <= $post_from && $t > $post_from) {
            rncbc_show_json(array(
                'error' => true,
                'msg' => __('Sorry, the court has been booked.', 'rncbc')
            ));
        }
    }


    rncbc_import('model-calendar-reservation');
    $paypal_available = rncbc_paypal_available(true);
    $is_member = $_POST['is_member'] == 1;
    $total = false;
    if($paypal_available) {
//		$from = str_replace(':', '', str_replace('30', '50', $_POST['from']));
//		$to = str_replace(':', '', str_replace('30', '50', $_POST['to']));
        //$booking_length = ($to - $from) / 50;
        $total = $total_slot * ($is_member ? $court->member_price : $court->price);
    }


    $info = array(
        'calendar_id' => $id,
        'court_id' => $court_id,
        'day' => date('Ymd', $day),
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'comments' => $_POST['comments'],
        'create_time' => time(),
        'reservation' => json_encode(array(
            'from' => $_POST['from'],
            'to' => $_POST['to'],
            'is_member' => $is_member,
        )),
    );

    if($total)
        $info['payment_total'] = $total;

    $id = RNCbcCalendarReservation::create($info);


    rncbc_send_mail($calendar->title, array(
        'day' => $info['day'],
        'from' => $_POST['from'],
        'to' => $_POST['to'],
        'court' => $court->name,
    ), $info);

    $payment_url = false;
    if($paypal_available) {
        $payment_url = rncbc_paypal_url("pay.php?id={$id}&ot=court&s=".md5($id.'_'.$info['create_time']));
    }

    rncbc_show_json(array(
        'error' => false,
        'payment_url' => $payment_url,
        'msg' => $calendar->success_tip ? $calendar->success_tip : __('Success! Thanks for your booking.', 'rncbc')
    ));
}

function rncbc_valid_coaching4booking($court_id, $day, $from, $to) {
    if(!rncbc_coaching_available())
        return true;

    if ( ! class_exists( 'RNCbcCoaching' ) )
        require_once(RNCBC_COACHING_DIR . '/libs/model-coaching.php');

    $coaching = RNCbcCoaching::findAll(array(
        'is_deleted' => 0,
        'court_id' => $court_id,
        'in_day' =>  date('Y-m-d', $day),
    ));

    $week_day = substr(strtolower(date('D', $day)), 0, 2);

    foreach($coaching as $c) {
        if(!$c->activities)
            continue;
        $activities = json_decode($c->activities, true);

        if(!$activities[$week_day])
            continue;

        $c_start = str_replace(':', '', $activities[$week_day]['start']);
        $c_end = str_replace(':', '', $activities[$week_day]['end']);

        if( $from > $c_start && $from < $c_end )
            rncbc_show_json(array(
                'error' => true,
                'msg' => 'Invalid from date'
            ));

        if( $to > $c_start && $to < $c_end )
            rncbc_show_json(array(
                'error' => true,
                'msg' => 'Invalid to date'
            ));
    }
}

add_action('wp_ajax_rncbc_ajax_booking', 'rncbc_ajax_booking');
add_action('wp_ajax_nopriv_rncbc_ajax_booking', 'rncbc_ajax_booking');

function rncbc_day_data() {
    $day = strtotime($_GET['day']);
    $calendar = intval($_GET['calendar_id']);
    if(!$day || !$calendar)
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Invalid params.'
        ));

    rncbc_import('model-calendar');
    $calendar = RNCbcCalendar::findByPk($calendar);

    if(!$calendar)
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'Invalid params.'
        ));

    $coaching_data = rncbc_coaching_data($calendar, $day);

    $coaching_json = array();
    foreach($coaching_data as $cd) {
        foreach($cd as $c) {
            $coaching_json['coaching_'.$c['id']] = $c;
        }
    }
    rncbc_show_json(array(
        'error' => false,
        'msg' => 'success',
        'html' => rncbc_court_table($calendar, $day, $coaching_data),
        'coaching_json' => $coaching_json,
    ));
}
add_action('wp_ajax_rncbc_day_data', 'rncbc_day_data');
add_action('wp_ajax_nopriv_rncbc_day_data', 'rncbc_day_data');


function rncbc_court_table($calendar, $day, $coaching_data) {
    rncbc_import('model-court');
    rncbc_import('model-calendar-reservation');

    $paypal_available = rncbc_paypal_available(true);
    if($paypal_available) {
        RNCbcCalendarReservation::clearUnpaidReservations();
    }

    $now = rncbc_time();
    $start = false;
    if(date('Y-m-d', $day) == date('Y-m-d', $now)) {
        $close_time = $now + $calendar->booking_window_close * 60;

        if(date('i', $close_time) < 30) {
            $start = date('H', $close_time).':30';
        } else {
            $start = (date('H', $close_time)).':00';
        }
    }

    $courts = RNCbcCourt::findAll($calendar->id);
    $day = date('Ymd', $day);
    $days = RNCbcCalendarReservation::findAllByDay($day);
    $reservations = array();
    foreach($days as $d) {
        if(!$reservations[$d->court_id])
            $reservations[$d->court_id] = array();

        $tmp = json_decode($d->reservation);
        $tmp->name = $d->name;

        $reservations[$d->court_id][] = $tmp;
    }




    $html = '<table class="rncbc_table" id="rncbc_table_'.$calendar->id.'" data-id="'.$calendar->id.'" data-day="'.$day.'" data-to="'.str_replace(':', '', $calendar->to).'"><tbody>';
    $html .= '<tr><th width="70"></th>';

    $courts_ids = array();
    foreach($courts as $c) {
        $courts_ids[] = $c->id;
        $html .= '<th id="court_'.$c->id.'" data-price="'.$c->price.'" data-member-price="'.$c->member_price.'">'.$c->name.'</th>';
    }
    $html .= '</tr>';

    $from = explode(':', $calendar->from);
    $to = explode(':', $calendar->to);

    $cols = count($courts);
    $slot = $calendar->time_slot ? $calendar->time_slot : 30;
    $row_span = 60/$slot;
//	$step = $calendar->time_slot;
    for($i = 0; $i<=24;$i++) {
        if($i<$from[0] || $i>$to[0])
            continue;

        $half_disable = false;
        if($i == $to[0] && $to[1] != 30) {
            $half_disable = true;
        }


        $time = str_pad($i, 2, '0', STR_PAD_LEFT);
        $html .= '<tr><td rowspan="'.$row_span.'">'.$time.':00</td>';

        $int_class = '';
        $name = '';
        $last = '';
        for($k=0;$k<$row_span;$k++) {
            $s = ($k*$slot);
            $t = $time.':'.str_pad(($k*$slot), 2, '0', STR_PAD_LEFT);
            $t_string = str_replace(':', '', $t);
            for($j=0;$j<$cols;$j++) {
                if($to[0].$to[1] <= $t_string) {
                    $html .= '<td  class="time_slot time_'.$courts_ids[$j].' u" data-court-id="'.$courts_ids[$j].'" data-time="'.$t.'"></td>';
                    continue;
                }


                if($coaching_data[$courts_ids[$j]]) {
                    $coaching_html = '';
                    foreach($coaching_data[$courts_ids[$j]] as $ca) {
                        $ca_start = str_replace(':', '', $ca['activities']['start']);
                        $ca_end = str_replace(':', '', $ca['activities']['end']);
                        if($t_string >= $ca_start && $t_string < $ca_end) {
                            $full = $ca['people'] >= $ca['capacity'] ? 'full' : '';
                            $coaching_html = '<td class="time_slot time_'.$courts_ids[$j].' coaching '.$full.'" data-court-id="'.$courts_ids[$j].'" data-coaching="'.$ca['id'].'" data-time="'.$t.'">'.($ca['coach_name'].' ('. $ca['people'].'/'.$ca['capacity'] .')').'</td>';
                        }
                    }
                    if($coaching_html) {
                        $html .= $coaching_html;
                        continue;
                    }
                }

                if($start && $start >= $time.'30') {
                    $half_disable = true;
                }

                $half_class = '';
                $last_name = '';
                if($reservations[$courts_ids[$j]]) {
                    // && !$half_disable
                    $tmp = str_replace(':', '', $t);
                    foreach($reservations[$courts_ids[$j]] as $r) {
                        $f = str_replace(':', '', $r->from);
                        $_t = str_replace(':', '', $r->to);
                        if($f <= $tmp && $_t > $tmp) {
                            $half_class = 'u';
                            $last_name = $r->name;
                            break;
                        }
                    }
                }

                if(!$half_disable && $half_class != 'u') {
                    $half_class = 'a';
                } else {
                    $half_class = 'u';
                }

                if(str_replace(':', '', $calendar->to) <= $time.$s)
                    $half_class = 'u';

                $last = '';
                if(str_replace(':', '', $calendar->to) <= $time.$s) {
                    $last = ' last';
                }

                $html .= '<td  class="time_slot time_'.$courts_ids[$j].' '.$half_class.$last.($last_name ? ' booked' : '').'" data-court-id="'.$courts_ids[$j].'" data-time="'.$t.'">'.($last_name ? $last_name : $t).'</td>';
            }
            $html .= '</tr>';
        }


    }
    $html .= '</tbody></table>';

    return $html;
}


function rncbc_coaching_data($calendar, $day) {

    $coaching_available = rncbc_coaching_available();
    $coaching_data = array();
    if($coaching_available) {
        if(!class_exists('RNCbcCoaching'))
            require_once(RNCBC_COACHING_DIR . '/libs/model-coaching.php');

        if(!class_exists('RNCbcCoachingReservation'))
            require_once(RNCBC_COACHING_DIR . '/libs/model-coaching-reservation.php');

        $coaching = RNCbcCoaching::findAll(array(
            'is_deleted' => 0,
            'calendar_id' => $calendar->id,
            'in_day' =>  date('Y-m-d', $day),
        ));


        $week_day = substr(strtolower(date('D', $day)), 0, 2);

        foreach($coaching as $c) {
            if(!$c->activities)
                continue;
            $activities = json_decode($c->activities, true);

            if(!$activities[$week_day])
                continue;

            if(!$coaching_data[$c->court_id])
                $coaching_data[$c->court_id] = array();

            $coaching_reservation = RNCbcCoachingReservation::reservation(array(
                'coaching_id' => $c->id,
                'day' => date('Ymd', $day),
            ));

            $tmp = array(
                'id' => $c->id,
                'title' => $c->title,
                'coach_name' => $c->coach_name,
                'capacity' => intval($c->capacity),
                'people' => intval($coaching_reservation),
                'description' => wpautop($c->description),
                'price' => floatval($c->price),
                'activities' => $activities[$week_day],
            );

            $coaching_data[$c->court_id][] = $tmp;
        }

    }

    return $coaching_data;
}


function rncbc_show_json($arr) {
    echo json_encode($arr);
    wp_die();
}




function rncbc_ajax_court() {
    if(!current_user_can('manage_options'))
        rncbc_show_json(array(
            'error' => true,
            'msg' => 'error'
        ));

    $price = NULL;
    $member_price = NULL;
    if($_GET['method'] != 'delete' && rncbc_paypal_available(true)) {
        $price = floatval($_POST['price']);
        $member_price = $_POST['member_price'] ? floatval($_POST['member_price']) : null;
        if(!$price)
            rncbc_show_json(array(
                'error' => true,
                'msg' => 'Invalid price.'
            ));
    }

    rncbc_import('model-court');
    if($_GET['method'] == 'create') {
        $calendar_id = intval($_POST['calendar_id']);
        if(rncbc_valid_license() !== TRUE) {
            $courts = RNCbcCourt::findAll($calendar_id);
            if(count($courts) >= 4)
                rncbc_show_json(array(
                    'error' => true,
                    'msg' => 'Sorry, Lite version only support 4 courts.'
                ));
        }

        $data = array(
            'name' => $_POST['name'],
            'calendar_id' => $calendar_id,
        );
        if($price) {
            $data['price'] = $price;
        }
        if($member_price) {
            $data['member_price'] = $member_price;
        }

        RNCbcCourt::create($data);

    } else if($_GET['method'] == 'update') {

        $data = array('name' => $_POST['name']);
        if($price) {
            $data['price'] = $price;
        }
        if($member_price) {
            $data['member_price'] = $member_price;
        }

        RNCbcCourt::update(intval($_POST['id']), $data);

    } else if($_GET['method'] == 'delete') {
        RNCbcCourt::update(intval($_POST['id']), array(
            'status' => 0,
        ));
    }


    rncbc_show_json(array(
        'error' => false,
        'msg' => 'success'
    ));
}

add_action('wp_ajax_rncbc_ajax_court', 'rncbc_ajax_court');


function rncbc_diff_minutes($from, $to) {
    $start = strtotime($from);
    $stop = strtotime($to);
    $diff = ($stop - $start);

    return $diff/60;
}