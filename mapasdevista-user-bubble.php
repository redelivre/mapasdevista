<?php
	global $user_loop;
	$metas = get_user_meta($user_loop->ID);
?>
<section id="entry-default" class="clearfix">
	<?php if(array_key_exists('organization', $metas) && count($metas['organization']) > 0 ) : ?>
		<div class="mapasdevista-user-bubble-organization">
			<div class="mapasdevista-user-bubble-title" >
				<?php _e('Organization', 'minka'); ?>
			</div>
			<div class="mapasdevista-user-bubble-text" >
				<?php echo $metas['organization'][0]; ?>
			</div>
		</div>
	<?php endif; ?>
	<?php if(array_key_exists('city', $metas) && count($metas['city']) > 0 ) : ?>
		<div class="mapasdevista-user-bubble-city">
			<div class="mapasdevista-user-bubble-title" >
				<?php _e('City', 'minka'); ?>
			</div>
			<div class="mapasdevista-user-bubble-text" >
				<?php echo $metas['city'][0]; ?>
			</div>
		</div>
	<?php endif; ?>
	<?php if(array_key_exists('country', $metas) && count($metas['country']) > 0 ) : ?>
		<div class="mapasdevista-user-bubble-country">
			<div class="mapasdevista-user-bubble-title" >
				<?php _e('Country', 'minka'); ?>
			</div>
			<div class="mapasdevista-user-bubble-text" >
				<?php echo $metas['country'][0]; ?>
			</div>
		</div>
	<?php endif; ?>
	<div class="mapasdevista-user-bubble-e-mail">
		<div class="mapasdevista-user-bubble-title" >
			<?php _e('E-mail', 'minka'); ?>
		</div>
		<div class="mapasdevista-user-bubble-text" >
			<?php echo $user_loop->user_email; ?>
		</div>
	</div>
</section>