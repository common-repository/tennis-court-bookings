<div class="wrap">
	<h2><?php _e('Court Booking Calendars', 'rncbc') ?></h2>
	<hr />

	<?php $list_table->display(); ?>

	<a href="<?php echo admin_url('admin.php?page=rncbc_calendars&action=create'); ?>" class="button button-primary button-large"><?php _e('Create new calender', 'rncbc') ?></a>
</div>