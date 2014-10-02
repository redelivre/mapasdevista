<div id="mapasdevista_load_bubbles" class="hide"><?php
$users = mapasdevista_get_users(1, $mapinfo)->get_results();
global $user_loop;
foreach ($users as $user) :
	$user_loop = $user;
?>
<div id="balloon_<?php echo $user->ID; ?>" class="result clearfix">
    <div class="balloon">
        
        <div class="content">
            <!--
            <p class="metadata bottom">
                <span class="date"><?php the_time( get_option('date_format') ); ?></span>
            </p>
            -->
            <h1 class="bottom"><a class="js-link-to-user" id="balloon-post-link-<?php echo $user->ID; ?>" href="<?php echo get_author_posts_url($user->ID, $user->user_name); ?>" <?php //onClick="mapasdevista.linkToPost(this); return false;" ?> ><?php echo $user->display_name; ?></a></h1>
            <?php mapasdevista_get_template( 'mapasdevista-user-bubble'); ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
