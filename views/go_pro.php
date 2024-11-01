<div class="wrap">
	<h2><?php _e('Go Pro', 'rncbc'); ?></h2>
	<hr />
	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options') ?>
	<table class="form-table">

		<tbody>

		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="rncbc_license_key"><?php _e('License key', 'rncbc') ?></label>
				<td class="forminp forminp-text">
					<input name="rncbc_license_key" id="rncbc_license_key" type="text" style="width:300px;" value="<?php echo $rncbc_license_key ?>" class="" />
					<?php if(rncbc_valid_license() !== true): ?>
						<p style="color: #ff3635">
                            <?php _e('Our Tennis court bookings Lite plugin is there for you to try it out.', 'rncbc') ?>
                            <br />
                            <?php _e('There will be an annoying message in your booking page encouraging you to Go Pro. Going Pro will get rid of this message and activates 12 months support and free updates.', 'rncbc') ?>
                            You can <a href="http://www.ezihosting.com/tennis-court-bookings/" target="_blank">Go Pro here</a>.
						</p>
					<?php else: ?>
						<p style="color: #0eb310">
                            <?php _e('Your License key is validated. Thank you for your purchase!', 'rncbc') ?>
						</p>
					<?php endif;?>
				</td>
		</tr>

		</tbody>
	</table>
		<p><input type="submit" class="button-primary" name="Submit" value="Save Changes" /></p>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="rncbc_license_key" />
	</form>

</div>