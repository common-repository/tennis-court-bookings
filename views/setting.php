<div class="wrap rncbc-wrap">
<h2><?php _e('Court Booking Calender Settings', 'rncbc') ?></h2>
	<br />
	<br />
	<h2 class="nav-tab-wrapper">
		<a href="javascript:;" class="nav-tab nav-tab-switch nav-tab-active" data-show="notifications">Notifications</a>
		<a href="javascript:;" class="nav-tab nav-tab-switch" data-show="paypal">PayPal</a>

	</h2>
	<br />
	<br />

<?php $is_pro = rncbc_valid_license() === TRUE;?>
<?php $default = rncbc_default_setting();?>
<div id="notifications" class="nav-tab-content">

	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options') ?>
		<table class="form-table">

			<tr>
				<th><?php _e('Confirmation to customer', 'rncbc') ?></th>
				<td>
					<label><input type="checkbox" name="rncbc_email2customer" value="1" <?php checked($rncbc_email2customer == 1) ?> /></label>
				</td>
			</tr>
			<?php if($is_pro):?>
				<tr>
					<th><?php _e('Subject', 'rncbc') ?></th>
					<td>
						<p><input type="text" class="regular-text" placeholder="<?php _e('Subject', 'rncbc') ?>" name="rncbc_email_customer_subject" value="<?php echo $rncbc_email_customer_subject ?>" /></p>
					</td>
				</tr>
				<tr>
					<th><?php _e('Body', 'rncbc') ?></th>
					<td>
						<?php wp_editor(  $rncbc_email_customer_body, 'rncbc_email_customer_body', $settings = array() ); ?>
						<p class="description">shortcode [booking_information]</p>
					</td>
				</tr>
			<?php else :?>
				<tr>
					<th><?php _e('Subject', 'rncbc') ?></th>
					<td>
						<p><input type="text" class="regular-text" placeholder="<?php _e('Subject', 'rncbc') ?>" name="rncbc_email_customer_subject" value="<?php echo $default['rncbc_email_customer_subject'] ?>" disabled="disabled" /></p>
					</td>
				</tr>
				<tr>
					<th><?php _e('Body', 'rncbc') ?></th>
					<td>
						<textarea name="rncbc_email_customer_body" style="width: 25em;" disabled="disabled" rows="4"><?php echo str_replace('<br />', "\r\n", $default['rncbc_email_customer_body']) ?></textarea>
					</td>
				</tr>
			<?php endif;?>

			<tr>
				<th><?php _e('Notification to admin', 'rncbc') ?></th>
				<td>
					<label><input type="checkbox" name="rncbc_email2admin" value="1" <?php checked($rncbc_email2admin == 1) ?> /></label>
				</td>
			</tr>
			<tr>
				<th><?php _e('Email address', 'rncbc') ?></th>
				<td>
					<p><input type="text" class="regular-text" placeholder="<?php _e('Email address', 'rncbc') ?>" name="rncbc_email_admin_address" value="<?php echo $rncbc_email_admin_address; ?>" /></p>
				</td>
			</tr>
			<?php if($is_pro):?>
				<tr>
					<th><?php _e('Subject', 'rncbc') ?></th>
					<td>
						<p><input type="text" class="regular-text" placeholder="Subject" name="rncbc_email_admin_subject" value="<?php echo $rncbc_email_admin_subject; ?>" /></p>
					</td>
				</tr>
				<tr>
					<th><?php _e('Body', 'rncbc') ?></th>
					<td>
						<?php wp_editor( $rncbc_email_admin_body, 'rncbc_email_admin_body', $settings = array() ); ?>
						<p class="description">shortcode [booking_information]</p>
					</td>
				</tr>

			<?php else :?>
				<tr>
					<th><?php _e('Subject', 'rncbc') ?></th>
					<td>
						<p><input type="text" class="regular-text" placeholder="<?php _e('Subject', 'rncbc') ?>" name="rncbc_email_admin_subject" value="<?php echo $default['rncbc_email_admin_subject'] ?>" disabled="disabled" /></p>
					</td>
				</tr>
				<tr>
					<th><?php _e('Body', 'rncbc') ?></th>
					<td>
						<textarea name="rncbc_email_admin_body" style="width: 25em;" disabled="disabled" rows="4"><?php echo str_replace('<br />', "\r\n", $default['rncbc_email_admin_body']) ?></textarea>
					</td>
				</tr>
			<?php endif;?>
		</table>

		<p><input type="submit" class="button-primary" name="Submit" id="setting-submit2" value="Save Changes" /></p>
		<input type="hidden" name="action" value="update" />
		<?php if($is_pro):?>
			<input type="hidden" name="page_options" value="rncbc_email2customer,rncbc_email_customer_subject,rncbc_email_customer_body,rncbc_email2admin,rncbc_email_admin_subject,rncbc_email_admin_body,rncbc_email_admin_address" />
		<?php else :?>
			<input type="hidden" name="page_options" value="rncbc_email2customer,rncbc_email2admin,rncbc_email_admin_address" />
		<?php endif;?>
	</form>
</div>

	<div id="paypal" class="nav-tab-content" style="display: none">
		<?php if(!$paypal_available):?>
		<p style="color: #ff3635">
			To activate PayPal Add-on. You can <a href="http://www.ezihosting.com/tennis-court-bookings/" target="_blank">Get more detail here</a>.
		</p>
		<?php endif;?>
		<form method="post" action="options.php">
			<?php wp_nonce_field('update-options') ?>
			<?php $paypal_disabled = $paypal_available ? '' : ' disabled="disabled"';?>
			<?php $paypal_keys = '';?>
			<table class="form-table">
				<?php foreach($paypal_form_fields as $k=>$v): ?>
					<?php
					$key = 'rncbc_paypal_'.$k;
					$paypal_keys .= ','.$key;
					?>
					<tr>
					<th><?php echo $v['title'] ?></th>
					<?php if($v['type'] == 'text'): ?>
					<td>
						<p><input <?php echo $paypal_disabled ?> type="text" class="regular-text" placeholder="<?php echo $v['placeholder'] ?>" name="<?php echo $key ?>" value="<?php echo get_option($key, $v['default']) ?>" /></p>
						<p class="description"><?php echo $v['description'] ?></p>
					</td>

					<?php elseif($v['type'] == 'checkbox'): ?>

							<td>
								<label><input <?php echo $paypal_disabled ?> type="checkbox" name="<?php echo $key ?>" value="1" <?php checked(get_option($key) == 1) ?> /> <?php echo $v['label'] ?></label>
								<p class="description"><?php echo $v['description'] ?></p>
							</td>
					<?php elseif($v['type'] == 'select'): ?>
						<td>
							<select name="<?php echo $key ?>" <?php echo $paypal_disabled ?>>
								<?php foreach($v['options'] as $ok=>$ov): ?>
									<option value="<?php echo $ok ?>" <?php selected(get_option($key, $v['default']) == $ok) ?>><?php echo $ov ?></option>
								<?php endforeach;?>
							</select>

						</td>
					<?php elseif($v['type'] == 'title'): ?>

					<?php endif;?>
					</tr>
				<?php endforeach; ?>

			</table>

				<p><input type="submit" <?php $paypal_disabled ?> class="button-primary" name="Submit" id="setting-submit2" value="Save Changes" /></p>
				<input type="hidden" name="action" value="update" />
				<?php if($paypal_available):?>
					<input type="hidden" name="page_options" value="<?php echo trim($paypal_keys, ',') ?>" />
				<?php endif;?>
		</form>
	</div>

</div>