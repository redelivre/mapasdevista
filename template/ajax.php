<?php

add_action('wp_ajax_nopriv_mapasdevista_get_posts', 'mapasdevista_get_posts_ajax');
add_action('wp_ajax_mapasdevista_get_posts', 'mapasdevista_get_posts_ajax');

add_action('wp_ajax_nopriv_mapasdevista_get_post', 'mapasdevista_get_post_ajax');
add_action('wp_ajax_mapasdevista_get_post', 'mapasdevista_get_post_ajax');

add_action('wp_ajax_nopriv_mapasdevista_get_posts_json', 'mapasdevista_get_posts_json');
add_action('wp_ajax_mapasdevista_get_posts_json', 'mapasdevista_get_posts_json');


function mapasdevista_get_post_ajax($p = null) {

    if (is_null($p) || !$p || strlen($p) == 0)
        $p = $_POST['post_id'];
        
    if (!is_numeric($p))
        die('error');
        
    query_posts('post_type=any&p='.$p);
    
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            mapasdevista_get_template('mapasdevista-loop-opened');
        }
    } else {
        die('error');
    }
    
    die();

}

function mapasdevista_get_posts_ajax() {
	ini_set("memory_limit", "2048M");
    $mapinfo = get_option('mapasdevista', true);

    if (!is_array($mapinfo['post_types']))
        return; // nothing to show

    if ($_POST['get'] == 'totalPosts') {


        global $wpdb;


        foreach ($mapinfo['post_types'] as $i => $p) {
            $mapinfo['post_types'][$i] = "'$p'";
        }

        $pt = implode(',', $mapinfo['post_types']);
        
        $search_query = '';
        
        if (isset($_POST['search']) && $_POST['search'] != '') {
            $serach_for = '%' . $_POST['search'] . '%';
            $search_query = $wpdb->prepare( "AND (post_title LIKE %s OR post_content LIKE %s )", $serach_for, $serach_for  );
        }
        
        if ($_POST['api'] == 'image') {
            $q = "SELECT COUNT(DISTINCT(post_id)) FROM $wpdb->postmeta JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.ID WHERE post_type IN ($pt) AND post_status = 'publish' AND meta_key = '_mpv_in_img_map' AND meta_value = '1' $search_query";
        } else {
            $q = "SELECT COUNT(post_id) FROM $wpdb->postmeta JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.ID WHERE post_type IN ($pt) AND post_status = 'publish' AND meta_key = '_mpv_inmap' AND meta_value = '1' $search_query";
        }

        $total = $wpdb->get_var($q);

        echo $total;


    } elseif ($_POST['get'] == 'posts') {

        if ($_POST['api'] == 'image') {

            $args = array(
                'numberposts'     => $_POST['posts_per_page'],
                'offset'          => $_POST['offset'],
                'orderby'         => 'post_date',
                'order'           => 'DESC',
                'meta_key'        => '_mpv_in_img_map',
                'meta_value'      => 1,
                'post_type'       => $mapinfo['post_types'],
            );

        } else {
            $args = array(
                'numberposts'     => $_POST['posts_per_page'],
                'offset'          => $_POST['offset'],
                'orderby'         => 'post_date',
                'order'           => 'DESC',
                'meta_key'        => '_mpv_inmap',
                'meta_value'      => 1,
                'post_type'       => $mapinfo['post_types'],
            );
        }
        
        if (isset($_POST['search']) && $_POST['search'] != '')
            $args['s'] = $_POST['search'];
        
        $posts = get_posts($args);

        $postsResponse = array();

        $number = $_POST['offset'];

        foreach ($posts as $post) {


            if ($_POST['api'] == 'image') {

                $meta = get_post_meta($post->ID, '_mpv_img_coord_' . $_POST['page_id'], true);
                $meta = explode(',', $meta);

                $location = array();
                $location['lon'] = floatval($meta[0]);
                $location['lat'] = floatval($meta[1]);

                $pin_id = get_post_meta($post->ID, '_mpv_img_pin_' . $_POST['page_id'], true);
                $pin = wp_get_attachment_image_src($pin_id, 'full');
                $pin['clickable'] = get_post_meta($pin_id, '_pin_clickable', true) !== 'no';

            } else {

                $location = get_post_meta($post->ID, '_mpv_location', true);

                // wordpress doesn't serialize data correctly and openlayers
                // only accept float values for latitude and longitude
                if (isset($location['lat']) && isset($location['lon'])) {
                    $location['lat'] = floatval($location['lat']);
                    $location['lon'] = floatval($location['lon']);
                }

                $pin_id = get_post_meta($post->ID, '_mpv_pin', true);
                $pin = wp_get_attachment_image_src($pin_id, 'full');
                $pin['anchor'] = get_post_meta($pin_id, '_pin_anchor', true);
                $pin['clickable'] = get_post_meta($pin_id, '_pin_clickable', true) !== 'no';
                
            }

            
                    
            $number ++;
            $terms = wp_get_object_terms( $post->ID, $mapinfo['taxonomies'] );

            
            
            $pResponse = array(
                'ID' => $post->ID,
                'title' => $post->post_title,
                'date' => $post->post_date,
                'location' => $location,
                'terms' => $terms,
                'post_type' => $post->post_type,
                'number' => $number,
                'author' => $post->post_author,
                'pin' => $pin
                
            );
            
            /*
            if($post->post_type == 'page' && get_post_meta($post->ID, '_mapasdevista')){
                $pResponse['link'] = get_post_permalink($post->ID);
            }
            */
            
            $postsResponse[] = $pResponse;


        }

        $newoffset = (int) $_POST['offset'] + sizeof($posts) < (int) $_POST['total'] ? (int) $_POST['offset'] + (int) $_POST['posts_per_page'] : 'end';

        $response = array(

            'newoffset' => $newoffset,
            'posts' => $postsResponse

        );

        echo json_encode($response);


    } 

    die();

}

function has_clickable_pin($post_id=null) {
    global $post;

    if (is_null($post_id) || !is_numeric($post_id)) {
        if (isset($post->ID) && is_numeric($post->ID))
            $post_id = $post->ID;
        else
            return false;
    }
    $pin_id = get_post_meta($post_id, '_mpv_pin', true);
    return get_post_meta($pin_id, '_pin_clickable', true) !== 'no';
}

function the_pin($post_id = null, $page_id = null) {

    global $post;
    
    
    
    if (is_null($post_id) || !is_numeric($post_id)) {
        if (isset($post->ID) && is_numeric($post->ID))
            $post_id = $post->ID;
        else
            return false;
    }
    
    $mapinfo = get_option('mapasdevista', true);
    
    if ($mapinfo['api'] == 'image') {
        $pin_id = get_post_meta($post_id, '_mpv_img_pin_' . $current_map_page_id, true);
                
    } else {
        $pin_id = get_post_meta($post_id, '_mpv_pin', true);
        
    }
    
    echo mapasdevista_get_pin($pin_id);
    
}

function mapasdevista_get_posts_json()
{
	ini_set("memory_limit", "2048M");
	$mapinfo = get_option('mapasdevista', true);
	
	$args = array(
			'numberposts'     => -1,
			//'offset'          => $_POST['offset'],
			'orderby'         => 'post_date',
			'order'           => 'DESC',
			'meta_key'        => '_mpv_inmap',
			'meta_value'      => 1,
			'post_type'       => $mapinfo['post_types'],
	);

	$posts = get_posts($args);
	
	//echo '<pre>';
		
	
	global $wpdb;
	
	$metas = array();
	$ret = array( );
	
	global $post;
	
	foreach ($posts as $post)
	{
		setup_postdata($post);
		//print_r($post);
		$querystr = "
		SELECT $wpdb->postmeta.meta_key,$wpdb->postmeta.meta_value  FROM $wpdb->posts
		INNER JOIN $wpdb->postmeta ON($wpdb->posts.ID = $wpdb->postmeta.post_id)
		WHERE
		$wpdb->posts.ID = $post->ID
		";
		
		$metas[$post->ID] = $wpdb->get_results($querystr, OBJECT_K);
		
		$metas[$post->ID]['_mpv_location']->meta_value = unserialize($metas[$post->ID]['_mpv_location']->meta_value);
		
		$uf = false;
		$cidade = false;
		$territorios = wp_get_post_terms(get_the_ID(), 'territorio');
		$spanCidade = '';
		$spanUf = '';
		foreach ($territorios as $territorio)
		{
			if($territorio->parent == 0) // Estado
			{
				$uf = $territorio->name;
			}
			else
			{
				$cidade = $territorio->name;
			}
		}
		if($cidade)
		{
			$spanCidade = '<span class="balloon-city">'. $cidade .'</span>';
		}
		if($uf)
		{
			$spanUf = '<span class="balloon-sep">&ndash;</span> <span class="balloon-uf">'.$uf.'</span>';
		}
		
		$spanBalloon_excerpt = '';
		
		$balloon_excerpt = get_the_excerpt();
		 
		if ( ! empty( $balloon_excerpt ) ) {
			$spanBalloon_excerpt = '<strong>Objetivos: </strong>' . $balloon_excerpt;
		}
		
		/*$content = '
			<div id="balloon_'. get_the_ID() .'" class="result clearfix">
			    <div class="balloon clearfix">
			        <div class="content">
			        	<header class="entry-header">
			            	<h1 class="bottom entry-title"><a class="pontos-js-link-to-post" id="balloon-post-link-'. get_the_ID() .'" href="'. get_permalink() .'" onClick="pontos_linkToPost(this); return false;">'. get_the_title(). '</a></h1>
			            	<div class="entry-meta">
			            		'. "pontosdecultura_the_terms( array( 'tipo' ) )" .'
			            		<em>em</em>
					            <span class="balloon-state-city entry-term">
						            '.$spanCidade.$spanUf.'
								</span>
						    </div><!-- .entry-meta -->
						</header>
			            <div class="balloon-entry-default clearfix" >
			            '.$spanBalloon_excerpt.'
			            </div>
			            '.'
		
			            <a href="pontos_linkToPost(this); return false;" class="read-more">Veja mais informações</a>
			        </div>
			    </div>
			</div><!-- #balloon -->
		';*/
		ob_start();
		?>
		<div id="balloon_<?php the_ID(); ?>" class="result clearfix">
		<div class="balloon clearfix">
		<div class="content">
		<header class="entry-header">
		<h1 class="bottom entry-title"><a class="pontos-js-link-to-post" id="balloon-post-link-<?php the_ID(); ?>" href="<?php the_permalink(); ?>" onClick="pontos_linkToPost(this); return false;"><?php the_title(); ?></a></h1>
			            	<div class="entry-meta">
			            		<?php pontosdecultura_the_terms( 'tipo' ); ?>
			            		<em>em</em>
					            <span class="balloon-state-city entry-term">
						            <?php
						            $uf = false;
						            $cidade = false;
						            $territorios = wp_get_post_terms(get_the_ID(), 'territorio');
						            foreach ($territorios as $territorio)
						            {
						            	if($territorio->parent == 0) // Estado
						            	{
						            		$uf = $territorio->name;
						            	}
						            	else
						            	{
						            		$cidade = $territorio->name;
						            	}
						            }
						            if($cidade)
						            {
						            	?>
										<span class="balloon-city"><?php echo $cidade; ?></span> 
										<?php
						            }
						            if($uf)
						            {
						            	?>
										<span class="balloon-sep">&ndash;</span> <span class="balloon-uf"><?php echo $uf; ?> </span>
										<?php
								    }
								    ?>
								</span>
						    </div><!-- .entry-meta -->
						</header>
			            <?php mapasdevista_get_template( 'mapasdevista-bubble', get_post_format() ); ?>
		
			            <a id="balloon-post-read-more-<?php the_ID(); ?>" href="<?php the_permalink(); ?>" onClick="pontos_linkToPost(this); return false;" class="read-more">Veja mais informações</a>
			        </div>
			    </div>
			</div><!-- #balloon -->
		<?php 
		
		$content = ob_get_clean();
		
		$ret[] = array(
				'type' => 'Feature',
				'properties' => array(
                        'name' => $post->post_title,
                        'content' => $content,
                        ),
                    "geometry" => array(
                        'type' => 'Point',
                        'coordinates' => array( 
                        				floatval($metas[$post->ID]['_mpv_location']->meta_value['lon']),
                        				floatval($metas[$post->ID]['_mpv_location']->meta_value['lat'])
                        )
                    )
                ); 
		
	}
	//print_r($metas);
	//echo '</pre>';

	echo json_encode($ret);
	die();
}