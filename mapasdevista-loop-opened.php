<?php
$format = get_post_format() ? get_post_format() : 'default';
?>


<div id="post_<?php the_ID(); ?>" class="entry <?php echo $format; ?> clearfix">

    <p class="metadata date bottom"><?php the_time( get_option('date_format') ); ?></p>
    <h1 class="bottom"><?php the_title(); ?></h1>
    <?php
    $map = get_option('mapasdevista_theme_options');
    if(!array_key_exists('show_authors', $map) || $map['show_authors'] == 'Y')
    { 
	    ?>
	    <p class="metadata author"><?php _e('Published by', 'mapasdevista'); ?>
	        <a class="js-filter-by-author-link" href="<?php echo get_author_posts_url( get_the_ID() ); ?>" id="post_overlay-author-link-<?php the_author_ID(); ?>" title="<?php esc_attr(the_author()); ?>"><?php the_author(); ?></a> | <?php edit_post_link( __( 'Edit', 'mapasdevista' ), '<span class="edit-link">', '</span>' ); ?>
	    </p>
	    <?php
	}
    ?>
    <?php mapasdevista_get_template( 'mapasdevista-content' ); ?>
    
</div>
