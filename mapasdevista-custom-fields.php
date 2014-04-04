<div class="mapasdevista-custom-fields-list">
<?php
foreach (get_post_custom() as $key => $value)
{
	if($key[0] != '_' && is_array($value) && count($value) > 0 && $value[0] != '')
	{
		?><span class="mapasdevista-custom-fields-item">
			<span class="mapasdevista-custom-fields-name">
				<?php echo $key; ?>
			</span>
			<span class="mapasdevista-custom-fields-value">
				<?php echo $value[0]; ?>
			</span>
			<span class="mapasdevista-custom-fields-separator">
				&#124;
			</span>
		</span><?php
	}
}
?>
</div>