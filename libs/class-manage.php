<?php
if ( ! defined( 'RNCBC_PLUGIN_DIR' ) ) {
    exit; // Exit if accessed directly
}
class RNCbcManageBase {

    protected function loadAssets() {
        wp_enqueue_script('rncbc-admin', plugins_url('assets/js/admin.js', RNCBC_PLUGIN_NAME), array('jquery'), RNCBC_PLUGIN_VERSION);
    }
}

class RNCbcManage extends RNCbcManageBase {

    public function setting() {
        $data = rncbc_setting();
        $data['paypal_available'] = rncbc_paypal_available();
        $data['paypal_form_fields'] = $this->paypal_form_fields();
        $this->loadAssets();
        rncbc_render('setting', $data);
    }


    public function calendars() {
        $action = $_GET['action'];

        $manage_calendar = new RNCbcManageCalendars();
        if($action && method_exists($manage_calendar, $action)) {
            $manage_calendar->$action();
        } else {
            $manage_calendar->index();
        }
    }


    public function go_pro() {
        $data = rncbc_setting();

        rncbc_render('go_pro', $data);
    }

    public function coaching() {
        if(class_exists('RNCbcManageCoachingReservation')) {
            $manage_coaching = new RNCbcManageCoachingReservation();
            $action = $_GET['action'];
            if($action && method_exists($manage_coaching, $action)) {
                $manage_coaching->$action();
            } else {
                $manage_coaching->index();
            }
        } else {
            rncbc_render('coaching_inactivate');
        }
    }

    private function paypal_form_fields() {
        return array(
            'enabled' => array(
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable PayPal standard',
                'default' => 'yes'
            ),
            'testmode' => array(
                'title'       => 'PayPal sandbox',
                'type'        => 'checkbox',
                'label'       => 'Enable PayPal sandbox',
                'default'     => 'no',
                'description' => sprintf( 'PayPal sandbox can be used to test payments. Sign up for a developer account <a href="%s">here</a>.', 'https://developer.paypal.com/' ),
            ),
            'advanced' => array(
                'title'       => 'Advanced options',
                'type'        => 'title',
                'description' => '',
            ),
            'currency' => array(
                'title'       => 'Currency',
                'type'        => 'select',
                'options'     => array(
                    'AUD' => 'Australian Dollar',
                    'BRL' => 'Brazilian Real',
                    'CAD' => 'Canadian Dollar',
                    'CZK' => 'Czech Koruna',
                    'DKK' => 'Danish Krone',
                    'EUR' => 'Euro',
                    'HKD' => 'Hong Kong Dollar',
                    'HUF' => 'Hungarian Forint',
                    'ILS' => 'Israeli New Sheqel',
                    'JPY' => 'Japanese Yen',
                    'MYR' => 'Malaysian Ringgit',
                    'MXN' => 'Mexican Peso',
                    'NOK' => 'Norwegian Krone',
                    'NZD' => 'New Zealand Dollar',
                    'PHP' => 'Philippine Peso',
                    'PLN' => 'Polish Zloty',
                    'GBP' => 'Pound Sterling',
                    'RUB' => 'Russian Ruble',
                    'SGD' => 'Singapore Dollar',
                    'SEK' => 'Swedish Krona',
                    'CHF' => 'Swiss Franc',
                    'TWD' => 'Taiwan New Dollar',
                    'THB' => 'Thai Baht',
                    'TRY' => 'Turkish Lira',
                    'USD' => 'U.S. Dollar',
                ),
                'description' => 'If your main PayPal email differs from the PayPal email entered above, input your main receiver email for your PayPal account here. This is used to validate IPN requests.',
                'default'     => 'USD',
            ),

            'invoice_prefix' => array(
                'title'       => 'Invoice Prefix',
                'type'        => 'text',
                'description' => 'Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.',
                'default'     => 'TC-',
                'desc_tip'    => true,
            ),
            'api_details' => array(
                'title'       => 'API Credentials',
                'type'        => 'title',
                'description' => sprintf( 'Enter your PayPal API credentials to process refunds via PayPal. Learn how to access your PayPal API Credentials %shere%s.', '<a href="https://developer.paypal.com/webapps/developer/docs/classic/api/apiCredentials/#creating-classic-api-credentials">', '</a>' ),
            ),
            'api_username' => array(
                'title'       => 'API Username',
                'type'        => 'text',
                'description' => 'Get your API credentials from PayPal.',
                'default'     => '',
                'desc_tip'    => true,
                'placeholder' => 'Required'
            ),
            'api_password' => array(
                'title'       => 'API Password',
                'type'        => 'text',
                'description' => 'Get your API credentials from PayPal.',
                'default'     => '',
                'desc_tip'    => true,
                'placeholder' => 'Required'
            ),
            'api_signature' => array(
                'title'       => 'API Signature',
                'type'        => 'text',
                'description' => 'Get your API credentials from PayPal.',
                'default'     => '',
                'desc_tip'    => true,
                'placeholder' => 'Required'
            ),
            'member_price_enabled' => array(
                'title'   => 'Member Price',
                'type'    => 'checkbox',
                'label'   => 'Enable member price',
                'default' => 'no'
            ),
        );
    }
}

class RNCbcManageCalendars extends RNCbcManageBase {
    public function index() {
        rncbc_import('class-calendar-list-table');

        $list_table = new RNCbcCalendarListTable();
        $list_table->prepare_items();

        rncbc_render('calendars', array(
            'list_table' => $list_table
        ));
    }

    public function create() {


        if($_POST['title']) {
            rncbc_import('model-calendar');

            $calendar = $this->processPost();

            $calendar['create_time'] = time();

            RNCbcCalendar::create($calendar);

            rncbc_admin_redirect('page=rncbc_calendars');
        } else {

            rncbc_load_yui();
            $this->loadAssets();
            rncbc_render('calendar_form', array(
                'calendar' => new stdClass()
            ));
        }

    }



    public function update() {
        rncbc_import('model-calendar');
        $id = intval($_GET['id']);

        $calendar = RNCbcCalendar::findByPk($id);
        if(!$calendar) {
            echo '<p>record not found.</p>';
        } else {
            if($_POST['title']) {

                $calendar = $this->processPost();
                RNCbcCalendar::update($id, $calendar);

                rncbc_admin_redirect('page=rncbc_calendars');
            } else {

                rncbc_load_yui();
                $this->loadAssets();
                rncbc_render('calendar_form', array(
                    'calendar' => $calendar
                ));
            }
        }
    }

    public function court() {
        rncbc_import('model-calendar');
        rncbc_import('model-court');
        $id = intval($_GET['id']);

        $calendar = RNCbcCalendar::findByPk($id);
        if(!$calendar) {
            echo '<p>record not found.</p>';
        } else {

            $courts = RNCbcCourt::findAll($id);


//			rncbc_load_yui();
            $this->loadAssets();
            rncbc_render('calendar_court', array(
                'calendar' => $calendar,
                'courts' => $courts,
            ));
        }
    }

    public function reservations() {
        rncbc_import('model-calendar');
        $id = intval($_GET['id']);

        $calendar = RNCbcCalendar::findByPk($id);
        if(!$calendar) {
            echo '<p>record not found.</p>';
        } else {

            rncbc_import('class-calendar-reservation-list-table');

            $list_table = new RNCbcCalendarReservationListTable();
            $list_table->prepare_items();

            rncbc_render('calendar_reservation', array(
                'calendar' => $calendar,
                'list_table' => $list_table,
            ));
        }
    }

    public function reservations_delete() {
        $id = intval($_GET['id']);

        if($id) {
            rncbc_import('model-calendar-reservation');
            RNCbcCalendarReservation::delete($id);
        }

        rncbc_admin_redirect('page=rncbc_calendars&action=reservations&id='.$_GET['calendar_id']);
    }

    public function delete() {
        $id = intval($_GET['id']);

        if($id) {
            rncbc_import('model-calendar');
            RNCbcCalendar::delete($id);
        }

        rncbc_admin_redirect('page=rncbc_calendars');
    }


    private function processPost() {
        $calendar = array();
        $calendar['title'] = $_POST['title'];
        $calendar['from'] = implode(':', $_POST['from']);
        $calendar['to'] = implode(':', $_POST['to']);
        if($_POST['min_day'] && strtotime($_POST['min_day']) !== -1)
            $calendar['min_day'] = $_POST['min_day'];

        if($_POST['max_day'])
            $calendar['max_day'] = $_POST['max_day'];

        $calendar['working_day'] = implode(',', $_POST['working_day']);

        $calendar['holiday'] = $_POST['holiday'];
        $calendar['booking_window_close'] = abs(intval($_POST['window_close']));

        $calendar['success_tip'] = trim($_POST['success_tip']);

        $calendar['step_size'] = intval($_POST['step_size']);
        $calendar['time_slot'] = intval($_POST['time_slot']);

        return $calendar;
    }



    public function coaching() {

        if(class_exists('RNCbcManageCoaching')) {
            $manage_coaching = new RNCbcManageCoaching();
            $action = $_GET['sub_action'];
            if($action && method_exists($manage_coaching, $action)) {
                $manage_coaching->$action();
            } else {
                $manage_coaching->index();
            }
        } else {
            rncbc_render('coaching_inactivate');
        }

    }
}