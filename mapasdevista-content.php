<section id="entry-content" class="clearfix">

    <?php the_content(); ?>

</section>

<footer class="entry-meta">
    <?php
        $categories_list = get_the_category_list( __( ', ', 'mapasdevista' ) );
        $tag_list = get_the_tag_list( '', __( ', ', 'mapasdevista' ) );
    ?>
    <!--
    <p>
        <?php if($categories_list) : ?>
            <?php _e("Categories: ", "mapasdevista"); echo $categories_list; ?>
        <?php endif; ?>

        <?php if($tag_list) : ?>
            <br/><?php _e("Tags: ", "mapasdevista"); echo $tag_list; ?>
        <?php endif; ?>
    </p>
    -->
    <?php mapasdevista_get_template( 'mapasdevista-custom-fields' ); ?>
</footer>
<div class="mapasdevista-comment-meta"> <?php
	if(comments_open())
	{?>
		<div class="mapasdevista-comment-tip">
			<h2><?php _e('Information is incomplete? Comment Here!', 'mapasdevista') ?></h2>
		</div><?php
	}
	comments_template(); ?>
</div>
