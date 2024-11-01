<div class="wrap">
	<h2><?php echo $calendar->title ?> <?php _e('Courts', 'rncbc') ?></h2>
	<hr />
	<?php
	$paypal_available = rncbc_paypal_available();
	$disable = $paypal_available ? '' : ' disabled="disabled"';
	?>
	<table class="form-table">
		<tr>
			<th>
				<label><?php _e('Court name', 'rncbc') ?>:</label>
				<input type="text" value="" name="name" placeholder="Court name" />
			</th>
			<th>
				<label><?php _e('Price (per time-slot)', 'rncbc') ?>:</label>
				<input type="text" value="" name="price" <?php echo $disable ?> placeholder="<?php _e('Price (per time-slot)', 'rncbc') ?>" />
			</th>
            <th>
                <label><?php _e('Member Price', 'rncbc') ?>:</label>
                <input type="text" value="" name="member_price" <?php echo $disable ?> placeholder="<?php _e('Member Price (per time-slot)', 'rncbc') ?>" />
            </th>
			<td>
				<br />
				<a href="javascript:;" class="button button-primary" data-calendar-id="<?php echo $calendar->id ?>" id="court-create"><?php _e('Create', 'rncbc') ?></a>
			</td>
		</tr>
		<?php foreach($courts as $c):?>
		<tr data-id="<?php echo $c->id ?>">
			<th>
				<input type="text" value="<?php echo $c->name ?>" name="name" placeholder="<?php _e('Court name', 'rncbc') ?>" />
			</th>
			<th>
				<input type="text" value="<?php echo $c->price ?>" name="price"  <?php echo $disable ?> placeholder="<?php _e('Price (pre time-slot)', 'rncbc') ?>" />
			</th>
            <th>
                <input type="text" value="<?php echo $c->member_price ?>" name="member_price"  <?php echo $disable ?> placeholder="<?php _e('Member Price (per time-slot)', 'rncbc') ?>" />
            </th>
			<td>
				<a href="javascript:;" class="button button-primary court-update"><?php _e('Update', 'rncbc') ?></a>
				<a href="javascript:;" class="button court-delete"><?php _e('Delete', 'rncbc') ?></a>
			</td>
		</tr>
		<?php endforeach;?>

	</table>

	<a href="<?php echo admin_url('admin.php?page=rncbc_calendars'); ?>" class="button"><?php _e('Back to calendars', 'rncbc') ?></a>
</div>