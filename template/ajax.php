<?php

add_action('wp_ajax_nopriv_mapasdevista_get_posts', 'mapasdevista_get_posts_ajax');
add_action('wp_ajax_mapasdevista_get_posts', 'mapasdevista_get_posts_ajax');

add_action('wp_ajax_nopriv_mapasdevista_get_post', 'mapasdevista_get_post_ajax');
add_action('wp_ajax_mapasdevista_get_post', 'mapasdevista_get_post_ajax');

add_action('wp_ajax_nopriv_mapasdevista_get_users', 'mapasdevista_get_users_ajax');
add_action('wp_ajax_mapasdevista_get_users', 'mapasdevista_get_users_ajax');

add_action('wp_ajax_nopriv_mapasdevista_get_user', 'mapasdevista_get_user_ajax');
add_action('wp_ajax_mapasdevista_get_user', 'mapasdevista_get_user_ajax');

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

function has_clickable_pin($post_id=null, $metafunction = 'get_post_meta')
{
	if($metafunction != 'get_post_meta' && $metafunction != 'get_user_meta' )
		wp_die( __( 'Cheatin&#8217; uh?' ) );
	
    global $post;

    if (is_null($post_id) || !is_numeric($post_id)) {
        if (isset($post->ID) && is_numeric($post->ID))
            $post_id = $post->ID;
        else
            return false;
    }
    $pin_id = $metafunction($post_id, '_mpv_pin', true);
    return $metafunction($pin_id, '_pin_clickable', true) !== 'no';
}

function the_pin($post_id = null, $page_id = null, $metafunction = 'get_post_meta')
{

	if($metafunction != 'get_post_meta' && $metafunction != 'get_user_meta' )
		wp_die( __( 'Cheatin&#8217; uh?' ) );
	
    global $post;
    
    
    
    if (is_null($post_id) || !is_numeric($post_id)) {
        if (isset($post->ID) && is_numeric($post->ID))
            $post_id = $post->ID;
        else
            return false;
    }
    
    $mapinfo = get_option('mapasdevista', true);
    
    if ($mapinfo['api'] == 'image') {
        $pin_id = $metafunction($post_id, '_mpv_img_pin_' . $current_map_page_id, true);
                
    } else {
        $pin_id = $metafunction($post_id, '_mpv_pin', true);
        
    }
    
    echo mapasdevista_get_pin($pin_id);
    
}

function mapasdevista_get_user_ajax($p = null)
{

	if (is_null($p) || !$p || strlen($p) == 0)
		$p = $_POST['post_id'];

	if (!is_numeric($p))
		die('error');

	$user = get_user_by('id', $p);

	/*if (have_users()) {
		while (have_users()) {
			the_user();
			mapasdevista_get_template('mapasdevista-user-opened');
		}
	} else {
		die('error');
	}*/

	die();

}

function mapasdevista_get_users_ajax() {
	ini_set("memory_limit", "2048M");
	$mapinfo = get_option('mapasdevista', true);

	if ($_POST['get'] == 'totalUsers') {


		global $wpdb;

		$search_query = '';

		if (isset($_POST['search']) && $_POST['search'] != '') {
			$serach_for = '%' . $_POST['search'] . '%';
			$search_query = $wpdb->prepare( "AND (user_nicename LIKE %s OR user_login LIKE %s OR ( meta_key IN ( 'first_name', 'last_name', 'nickname' ) AND meta_value LIKE %s ) )", $serach_for, $serach_for, $serach_for  );
		}

		if ($_POST['api'] == 'image') {
			$q = "SELECT COUNT(DISTINCT(user_id)) FROM $wpdb->usermeta JOIN $wpdb->users ON $wpdb->usermeta.user_id = $wpdb->users.ID WHERE user_status = 0 AND meta_key = '_mpv_in_img_map' AND meta_value = '1' $search_query";
		} else {
			$q = "SELECT COUNT(user_id) FROM $wpdb->usermeta JOIN $wpdb->users ON $wpdb->usermeta.user_id = $wpdb->users.ID WHERE user_status = 0 AND meta_key = '_mpv_inmap' AND meta_value = '1' $search_query";
		}

		$total = $wpdb->get_var($q);

		echo $total;


	}
	elseif ($_POST['get'] == 'users')
	{

		$args = array(
				'number'		=> $_POST['users_per_page'],
				'offset'        => $_POST['offset'],
		);


		if (isset($_POST['search']) && $_POST['search'] != '')
			$args['search'] = $_POST['search'];

		$users = mapasdevista_get_users(1, $mapinfo, $args)->get_results();

		$usersResponse = array();

		$number = $_POST['offset'];

		foreach ($users as $user)
		{
			if ($_POST['api'] == 'image') {

				$meta = get_user_meta($user->ID, '_mpv_img_coord_' . $_POST['page_id'], true);
				$meta = explode(',', $meta);

				$location = array();
				$location['lon'] = floatval($meta[0]);
				$location['lat'] = floatval($meta[1]);

				$pin_id = get_user_meta($user->ID, '_mpv_img_pin_' . $_POST['page_id'], true);
				$pin = wp_get_attachment_image_src($pin_id, 'full');
				$pin['clickable'] = get_post_meta($pin_id, '_pin_clickable', true) !== 'no';

			} else {

				$location = get_user_meta($user->ID, '_mpv_location', true);

				// wordpress doesn't serialize data correctly and openlayers
				// only accept float values for latitude and longitude
				if (isset($location['lat']) && isset($location['lon'])) {
					$location['lat'] = floatval($location['lat']);
					$location['lon'] = floatval($location['lon']);
				}

				$pin_id = get_user_meta($user->ID, '_mpv_pin', true);
				$pin = wp_get_attachment_image_src($pin_id, 'full');
				
				$params = array('http' => array(
						'method' => 'HEAD'
				));
				$ctx = stream_context_create($params);
				$pin_f = @fopen($pin[0], 'rb', false, $ctx);
				if ($pin_f == false)
				{ 
					$pin[0] = '';
				}
				
				$pin['anchor'] = get_post_meta($pin_id, '_pin_anchor', true);
				$pin['clickable'] = get_post_meta($pin_id, '_pin_clickable', true) !== 'no';

			}



			$number ++;

			$pResponse = array(
					'ID' => $user->ID,
					'nicename' => $user->user_nicename,
					'location' => $location,
					'number' => $number,
					'pin' => $pin
			);

			$usersResponse[] = $pResponse;
		}

		$newoffset = (int) $_POST['offset'] + sizeof($users) < (int) $_POST['total'] ? (int) $_POST['offset'] + (int) $_POST['users_per_page'] : 'end';

		$response = array(

				'newoffset' => $newoffset,
				'users' => $usersResponse

		);

		echo json_encode($response);


	}

	die();

}
