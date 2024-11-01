<div class="wrap">
	<h2><?php echo $calendar->title ?> <?php _e('Reservations', 'rncbc') ?></h2>
	<hr />

	<?php $list_table->display(); ?>

	<a href="<?php echo admin_url('admin.php?page=rncbc_calendars'); ?>" class="button"><?php _e('Back to calendars', 'rncbc') ?></a>
</div>