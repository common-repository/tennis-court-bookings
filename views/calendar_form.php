<style>
	.yui3-widget-loading { display:none; }
	.rncbc-timeslots {

		padding-top: 10px;
	}
	.rncbc-timeslots .item {
		display: inline-block;
		padding: 5px 0px 5px 10px;
		margin: 0px 10px 10px 0px;
		background-color: #21c245;
		color: #fff;
		border-radius: 2px;
		font-size: 12px;
	}
	.rncbc-timeslots .item a {
		color: #fff;
		text-decoration: none;
		display: inline-block;
		border-left: 1px solid #fff;
		padding:0px 8px;
		margin-left: 5px;
	}
	.yui3-button.notice {
		background-color: #1B7AE0;
		color: white;
	}
</style>
<div class="wrap rncbc-wrap ">
	<h2><?php _e($calendar->id ? 'Update Calendar' : 'Create Calendar', 'rncbc') ?></h2>
	<hr />


	<form action="" method="post" id="calender_form">
		<?php wp_nonce_field() ?>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label><?php _e('Title', 'rncbc') ?> <span>*</span></label>
				</th>
				<td>
					<input type="text" placeholder="<?php _e('Title', 'rncbc') ?>" value="<?php echo addslashes($calendar->title) ?>" id="" name="title" size="60" />
					<p class="description"><?php _e('Calendar name, this will also be send to customer\'s email.', 'rncbc') ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label><?php _e('Opening', 'rncbc') ?> <span>*</span></label>
				</th>
				<td>
					<?php
						if($calendar->from) {
							$from = explode(':', $calendar->from);
						} else {
							$from = array(6, 0);
						}
					?>
					<select name="from[]">
						<?php for($i=0;$i<24;$i++):?>
							<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?php selected($i==$from[0]); ?>><?php echo $i ?></option>
						<?php endfor;?>
					</select> : <select name="from[]">
						<option value="00">00</option>
						<option value="30" <?php selected($i==$from[1]); ?>>30</option>
					</select>
					<p class="description"></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label><?php _e('Closing', 'rncbc') ?> <span>*</span></label>
				</th>
				<td>
					<?php
					if($calendar->to) {
						$to = explode(':', $calendar->to);
					} else {
						$to = array(23, 0);
					}
					?>
					<select name="to[]">
						<?php for($i=0;$i<24;$i++):?>
							<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?php selected($i==$to[0]); ?>><?php echo $i ?></option>
						<?php endfor;?>
					</select> : <select name="to[]">
						<option value="00">00</option>
						<option value="30" <?php selected($i==$to[1]); ?>>30</option>
					</select>
					<p class="description"></p>
				</td>
			</tr>

			<?php $day = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');?>
			<tr>
				<th scope="row"><?php _e('Working Day', 'rncbc') ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e('Working Day', 'rncbc') ?></span></legend>
						<?php $working_day = $calendar->id ? explode(',', $calendar->working_day) : array('mo', 'tu', 'we', 'th', 'fr') ?>
						<?php foreach($day as $k=>$d):?>
							<?php $short_name = substr($d, 0, 2);$checked = in_array($short_name, $working_day);?>
						<label for="wd_<?php echo $d ?>">
							<input type="checkbox" data-eq="<?php echo $k ?>" <?php checked($checked); ?> id="wd_<?php echo $d ?>" value="<?php echo substr($d, 0, 2) ?>" name="working_day[]"> <?php echo ucfirst($d) ?>
						</label>

						<?php endforeach;?>

					</fieldset>
				</td>
			</tr>

			<?php
				$holiday = $calendar->holiday ? $calendar->holiday : '';
			?>
			<tr>
				<th scope="row">
					<label><?php _e('Holiday', 'rncbc') ?></label>
				</th>
				<td>

					<div style="width: 600px;">
						<div id="holiday-calendar" style="margin:0"></div>
					</div>
					<input type="hidden" name="holiday" value="<?php echo $holiday; ?>" />
					<p class="description"><?php _e('Public holidays or other days that bookings are not accepted.', 'rncbc') ?></p>
				</td>
			</tr>



			<tr>
				<th scope="row">
					<label><?php _e('Booking window closed time', 'rncbc') ?> <span>*</span></label>
				</th>
				<td>
					<input type="text" placeholder="" value="<?php echo $calendar->booking_window_close ?>" id="" name="window_close" size="6" />
					<p class="description"><?php _e('Set the number of minutes before the booking are not accept, default is 2 hours. PS. 1 hour = 60 minutes, 1 day = 1440 minutes', 'rncbc') ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label><?php _e('Minimum available date', 'rncbc') ?></label>
				</th>
				<td>
					<input type="text" placeholder="" value="<?php echo $calendar->min_day ?>" id="" name="min_day" size="10" class="datepicker" />
					<p class="description"><?php _e('Default is today, a formatted date string, eg..', 'rncbc');echo ' '.date('m/d/Y'); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label><?php _e('Maximum available date', 'rncbc') ?></label>
				</th>
				<td>
					<input type="text" placeholder="" value="<?php echo $calendar->max_day ?>" id="" name="max_day" size="10" class="datepicker" />
					<p class="description"><?php _e('Default is unlimited, +n means n days after today', 'rncbc') ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label><?php _e('Time slot', 'rncbc') ?></label>
				</th>
				<!--  -->
				<td>
					<select name="time_slot">
<!--						<option value="10" --><?php //selected($calendar->time_slot == 10) ?><!-->10 Minutes</option>-->
<!--						<option value="15" --><?php //selected($calendar->time_slot == 15) ?><!-->15 Minutes</option>-->
						<option value="30" <?php selected($calendar->time_slot == 30) ?>>30 Minutes</option>
<!--						<option value="60" --><?php //selected($calendar->time_slot == 60) ?><!-->60 Minutes</option>-->
					</select>
					<p class="description"></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label><?php _e('Step size', 'rncbc') ?></label>
				</th>
				<!-- $calendar->max_day -->
				<td>
					<select name="step_size">
						<?php for ($i=1;$i<=8;$i++): ?>
						<option value="<?php echo $i ?>" <?php selected($calendar->step_size == $i) ?>><?php echo $i ?> slot</option>
						<?php endfor; ?>
					</select>
					<p class="description"><?php _e('The booking time slots equals Time slot * Step size.', 'rncbc') ?></p>
				</td>
			</tr>


			<tr>
				<th scope="row">
					<label><?php _e('Success tip', 'rncbc') ?></label>
				</th>
				<td>
					<input type="text" placeholder="" value="<?php echo $calendar->success_tip ?>" id="" name="success_tip" size="60" class="" />
					<p class="description"><?php _e('The booking success pop-up message.', 'rncbc') ?></p>
				</td>
			</tr>

			<script>

				<?php
					if($holiday) {
						echo 'window.holidayDefault = '. json_encode(explode(',', $calendar->holiday)) .';';
					} else {
						echo 'window.holidayDefault = [];';
					}

					$special = $calendar->special_day ? $calendar->special_day : '';
					if($special) {
						echo 'window.specialDefault = '. $special .';';
					} else {
						echo 'window.specialDefault = {};';
					}
				?>
			</script>
			</tbody>
		</table>

		<p class="submit"><input type="button" value="Save Calender" id="calendar_save" class="button-primary" name="Submit"></p>
	</form>

</div>


	<div id="panelContent" class="yui3-widget-loading">
		<div class="yui3-widget-bd">
			<fieldset id="special-day-panel">
				<h3 id="special-title"></h3>
				<p>
					Add time slots
					<select name="hour">
						<?php for($i=0;$i<24;$i++):?>
							<option value="<?php echo $i ?>" <?php selected($i==9); ?>><?php echo $i ?></option>
						<?php endfor;?>
					</select> : <select name="minutes">
							<option>00</option>
							<option>30</option>
					</select>
					<input type="hidden" name="capacity" id="" value="1" placeholder="" size="2" />
					<button class="button add-time-slots" type="button" data-parent="special-day-panel" data-name="special_time[]">add</button>
				</p>

				<div class="rncbc-timeslots" id="special-slots">

				</div>
			</fieldset>
		</div>
	</div>
