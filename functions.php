<?php

// includes
include('admin/maps.php');
include('admin/pins.php');
include('admin/theme.php');
include('admin/metabox.php');
include('template/ajax.php');

add_action('init', function() { 

    global $current_blog, $campaign;
    
    if (!$campaign) {
        return;
    }
    
    $capabilities = Capability::getByPlanId($campaign->plan_id);
    
    if ($current_blog->blog_id > 1 && isset($capabilities->georreferenciamento) && $capabilities->georreferenciamento->value == 1 )  { 
        mapasdevista_regiser_post_type(); 
        add_action( 'admin_menu', 'mapasdevista_admin_menu' );
        add_action( 'admin_init', 'mapasdevista_admin_init' );
    } else {
        return;
    }
    
    // activate for each blog:
    if (get_option('mapasdevista_activaded') != 4) {
        update_option('mapasdevista_activaded', 4);
        mapasdevista_set_default_settings();
        mapasdevista_flush_rules();
        mapasdevista_set_default_menu();
        include('import-default-pins.php');
    }
});

function mapasdevista_set_default_menu() {

    $menu = get_term_by('slug', 'main', 'nav_menu');
    if ($menu && is_object($menu) && !is_wp_error($menu)) {
        $current = get_theme_mod( 'nav_menu_locations' );
        $current['mapasdevista_top'] = $menu->term_id;
        set_theme_mod( 'nav_menu_locations', $current );
    }    
}

function mapasdevista_set_default_settings() {

    $defaults = array(
        
        'name' => 'Mapa',
        'api' => 'googlev3',
        'type' => 'road',
        'coord' => Array
            (
                'lat' => '-15.050826166796774',
                'lng' => '-54.4263372014763'
            ),

        'zoom' => 4,
        'control' => Array
            (
                'zoom' => 'large',
                'pan' => 'on',
                'map_type' => 'on'
            ),
        'logical_operator' => 'OR',
        'post_types' => array('mapa'),
        'taxonomies' => array('categoria-mapa'),
        'visibility' => 'private',
    	'filters' => array(),
    	'show_authors' => 'Y'
    );
    
    update_option('mapasdevista', $defaults);

}

function mapasdevista_flush_rules() {

    global $wp_rewrite;
    $wp_rewrite->flush_rules();

}

load_plugin_textdomain( 'mapasdevista', WP_CONTENT_DIR . '/plugins/mapasdevista/languages/', basename(dirname(__FILE__)) . '/languages/' );

add_action( 'after_setup_theme', 'mapasdevista_setup' );
if ( ! function_exists( 'mapasdevista_setup' ) ):

    function mapasdevista_setup() {

        // Post Format support. You can also use the legacy "gallery" or "asides" (note the plural) categories.
        add_theme_support( 'post-formats', array( 'gallery', 'image', 'video' , 'audio'  ) );

        // This theme uses post thumbnails
        add_theme_support( 'post-thumbnails' );


        // This theme uses wp_nav_menu() in one location.
        register_nav_menus( array(
            'mapasdevista_top' => __( 'Map Menu (top)', 'mapasdevista' ),
            'mapasdevista_side' => __( 'Map Menu (side)', 'mapasdevista' )
        ) );
        
        add_image_size('mapasdevista-thumbnail',270,203,true);

    }

endif;

function mapasdevista_get_pin($pin_id, $size = 'thumbnail', $icon = false, $attr = '')
{
	if($pin_id < 6 || $pin_id > 19)
	{
		return wp_get_attachment_image($pin_id, $size, $icon, $attr);
	}
	else
	{
		$pin_image = wp_get_attachment_image($pin_id, $size, $icon, $attr);
		$pos = strpos($pin_image, 'alt="') + 5;
		$end = strpos($pin_image, '"', $pos);
		$file = substr($pin_image, $pos, $end - $pos);

		$pin_html = substr($pin_image, 0, strpos($pin_image, '/files/')).'/wp-content/plugins/mapasdevista/default-pins/'.$file.'">';
		return $pin_html;
	}
}

function mapasdevista_admin_menu() {

    add_submenu_page('edit.php?post_type=mapa', __('Configuração do mapa', 'mapasdevista'), __('Configuração do mapa', 'mapasdevista'), 'publish_posts', 'mapasdevista_maps', 'mapasdevista_maps_page');
    //add_menu_page(__('Maps of view', 'mapasdevista'), __('Maps of view', 'mapasdevista'), 'publish_posts', 'mapasdevista_maps', 'mapasdevista_maps_page',null,30);
    add_submenu_page('edit.php?post_type=mapa', __('Layout', 'mapasdevista'), __('Layout', 'mapasdevista'), 'publish_posts', 'mapasdevista_theme_page', 'mapasdevista_theme_page');
    add_submenu_page('edit.php?post_type=mapa', __('Pins', 'mapasdevista'), __('Pins', 'mapasdevista'), 'publish_posts', 'mapasdevista_pins_page', 'mapasdevista_pins_page');

    //add_submenu_page('edit.php?post_type=mapa', __('Importar Sql', 'mapasdevista'), __('Importar Sql', 'mapasdevista'), 'publish_posts', 'ImportarSql', 'mapasdevista_ImportarSql');
    if(is_super_admin()) add_submenu_page('edit.php?post_type=mapa', __('Importar Csv', 'mapasdevista'), __('Importar Csv', 'mapasdevista'), 'publish_posts', 'ImportarCsv', 'mapasdevista_ImportarCsv');
}


function mapasdevista_ImportarSql()
{
	ini_set("memory_limit", "2048M");
	set_time_limit(0);
	
	global $wpdb;
	
	$query = "SELECT * FROM mapacompleto;";
	
	//$wpdb->query($query);
	
	$rows = $wpdb->get_results($query);
	
	foreach ($rows as $row)
	{
		$post = array(
				'post_author'    => 1, //The user ID number of the author.
				'post_content'   => $row->text,
				'post_title'     => $row->proj, //The title of your post.
				'post_type'      => 'mapa',
				'post_status'	 => 'publish'
		);
		
		$post_id = wp_insert_post($post);
		
		if( is_int($post_id) )
		{
			$location = array();
			$location['lat'] = floatval(sprintf("%f", $row->lat));
			$location['lon'] = floatval(sprintf("%f", $row->lon));
			
			if($location['lat'] !== floatval(0) && $location['lon'] !== floatval(0))
			{
				update_post_meta($post_id, '_mpv_location', $location);
			}
			else
			{
				delete_post_meta($post_id, '_mpv_location');
			}
			
			$pin_id = substr($row->icon, 4);
			
			$pin_id = intval(sprintf("%d", $pin_id));
			
			if($pin_id > 0)
			{
				update_post_meta($post_id, '_mpv_pin', $pin_id);
			}
			
			delete_post_meta($post_id, '_mpv_inmap');
			delete_post_meta($post_id, '_mpv_in_img_map');
			add_post_meta($post_id, "_mpv_inmap", 1);
			
		}
		
	}
	
	//echo '<pre>'.var_dump($rows).'</pre>';
	
}

function mapasdevista_enqueue_scripts($mapinfo = array(), $post_type = null)
{
	if(!array_key_exists('api', $mapinfo))
	{
		$mapinfo = get_option('mapasdevista', true);
	}
	if(is_null($post_type))
	{
		$post_type = get_post_type();
	}
	
	wp_enqueue_script('mapstraction', mapasdevista_get_baseurl('template_directory') . '/js/mxn/mxn-min.js' );
	wp_enqueue_script('mapstraction-core', mapasdevista_get_baseurl('template_directory') . '/js/mxn/mxn.core-min.js');
	
	if ($mapinfo['api'] == 'openlayers') {
		wp_enqueue_script('openlayers', 'http://openlayers.org/api/OpenLayers.js');
		wp_enqueue_script('mapstraction-openlayers', mapasdevista_get_baseurl('template_directory') . '/js/mxn/mxn.openlayers.core-min.js');
	} elseif ($mapinfo['api'] == 'googlev3') {
	
		$googleapikey = get_mapasdevista_theme_option('google_key');
		$googleapikey = $googleapikey ? "&key=$googleapikey" : '';
		wp_enqueue_script('google-maps-v3', 'http://maps.google.com/maps/api/js?sensor=false' . $googleapikey);
		wp_enqueue_script('mapstraction-googlev3', mapasdevista_get_baseurl('template_directory') . '/js/mxn/mxn.googlev3.core-min.js');
		wp_enqueue_script('google-infobox', mapasdevista_get_baseurl('template_directory') . '/js/mxn/infobox_packed.js', array('mapstraction-googlev3'));
	
	} elseif ($mapinfo['api'] == 'image') {
		wp_enqueue_script('mapstraction-image', mapasdevista_get_baseurl('template_directory') . '/js/mxn/mxn.image.core.js');
	}

}

function mapasdevista_city_name_format($city)
{
	return str_replace(array('z'), array('s'), strtolower(remove_accents($city)));
	// Brazil -> Brasil, Luiz -> Luis
}

function mapasdevista_check_coords($location, $local, $country = false)
{
	$url = "https://maps.googleapis.com/maps/api/geocode/json?sensor=false&latlng=";//40.714224,-73.961452&
		
	$json = file_get_contents($url.$location['lat'].",".$location['lon']);
	$ret = json_decode($json, true);
	//echo strtolower(remove_accents($row[17]));
		
	if(is_array($ret) && array_key_exists('results', $ret) && is_array($ret['results']) && array_key_exists(0, $ret['results']))
	{
		$results = $ret['results'];
		foreach ($results as $result)
		{
			foreach ($result['address_components'] as $key => $value)
			{
				if($country)
				{
					
					if($value['types'][0] == 'country' )//|| $value['types'][0] == 'administrative_area_level_2')
					{
						echo "|".mapasdevista_city_name_format($value['long_name'])."| vs |".mapasdevista_city_name_format($local)."|\n";
						if(mapasdevista_city_name_format($value['long_name']) == mapasdevista_city_name_format($local))
						{
							return true;
						}
						break;
					}
				}
				else 
				{
					if($value['types'][0] == 'locality' || $value['types'][0] == 'administrative_area_level_2')
					{
						echo "|".mapasdevista_city_name_format($value['long_name'])."| vs |".mapasdevista_city_name_format($local)."|\n";
						if(mapasdevista_city_name_format($value['long_name']) == mapasdevista_city_name_format($local))
						{
							return true;
						}
						else
						{
							//workaround rio as rio de jainero on google database
							if(mapasdevista_city_name_format($value['long_name']) == 'rio' && mapasdevista_city_name_format($local) == "rio de janeiro")
							{
								return true;
							}
							//workaround diamante do oeste on google database
							if(mapasdevista_city_name_format($value['long_name']) == 'diamante d\'oeste' && mapasdevista_city_name_format($local) == "diamante do oeste")
							{
								return true;
							}
							var_dump($result['address_components'])."\n";
							echo "|".mapasdevista_city_name_format($value['long_name'])."| diff |".mapasdevista_city_name_format($local)."|\n";
						}
						break;
					}
				}
			}
			echo '-----------------------------Não Achou Em---------------------------------------------------------------------\n';
			var_dump($result['address_components']);
			echo '\n-----------------------------Não Achou Em---------------------------------------------------------------------\n';
		}
		return false;
	}
	else 
	{
		echo '---------------------------------------Sem retorno para--------------------------------------------------------------\n';
		var_dump($ret);
		echo '\n---------------------------------------Sem retorno para--------------------------------------------------------------\n';
	}
	return false;
} 

function mapasdevista_cords_check_loop($location, $local, $country = false)
{
	//print_r($location);
	
	if(!mapasdevista_check_coords($location, $local, $country))
	{
		$location_lat = array();
		$location_lat['lat'] = $location['lat']/10;
		$location_lat['lon'] = $location['lon'];echo "try lat/10:".$location_lat['lat']."/".$location_lat['lon']."\n";
		if(!mapasdevista_check_coords($location_lat, $local, $country))
		{
			$location_lon = array();
			$location_lon['lat'] = $location['lat'];
			$location_lon['lon'] = $location['lon']/10;echo "try lon/10:".$location_lon['lat']."/".$location_lon['lon']."\n";
			if(!mapasdevista_check_coords($location_lon, $local, $country))
			{
				$location_lat_lon = array();
				$location_lat_lon['lat'] = $location['lat']/10;
				$location_lat_lon['lon'] = $location['lon']/10;echo "try lat/10 and lon/10:".$location_lat_lon['lat']."/".$location_lat_lon['lon']."\n";

				if(!mapasdevista_check_coords($location_lat_lon, $local, $country))
				{
					$location = $location_lat_lon;
				}
				else
				{
					return false;
				}
			}
			else
			{
				$location = $location_lon;
			}
		}
		else
		{
			$location = $location_lat;
		}
	}
	return $location;
	
}

function mapasdevista_ImportarCsv()
{
	$debug = false;
	ini_set("memory_limit", "2048M");
	set_time_limit(0);

	$file = fopen ( '/tmp/import.csv', 'r');
	echo '<pre>';
	
	$row = fgetcsv( $file, 0, ';');
	$names = $row;
	$row = fgetcsv( $file, 0, ';');
	$i = 0;
	do
	{
		echo $row[0];
		
		$post = array(
				'post_author'    => 1, //The user ID number of the author.
				'post_content'   => $row[25],
				'post_title'     => $row[3], //The title of your post.
				'post_type'      => 'mapa',
				'post_status'	 => 'publish'
		);

		if(!$debug) $post_id = wp_insert_post($post);
		
		$no_import = array(25, 3, 11, 12);
		
		if(!$debug && is_int($post_id) )
		{
			
			$location = array();
			
			$row[11] = preg_replace('/[^0-9-]/','',$row[11]);
			$row[12] = preg_replace('/[^0-9-]/','',$row[12]);
			
			$offset = $row[11][0] == '-' ? 3 : 2;
			$row[11] = substr($row[11], 0, $offset).".".substr($row[11], $offset);

			$offset = $row[12][0] == '-' ? 3 : 2;
			$row[12] = substr($row[12], 0, $offset).".".substr($row[12], $offset);
			
			$location['lat'] = $row[11];
			$location['lon'] = $row[12];
				
			if($location['lat'] !== floatval(0) && $location['lon'] !== floatval(0))
			{
				echo "-----------------------------------------------------{$row[0]}----------------------------------------------------------------------\n";
				$location_ret = mapasdevista_cords_check_loop($location, $row[17]);
				if($location_ret === false)
				{
					//Vamos tentar por o ponto no Brasil pelo menos.
					$location_ret = mapasdevista_cords_check_loop($location, 'brasil', true);
					if($location_ret !== false)
					{
						$location = $location_ret;
					}
					else
					{
						echo $row[17]."\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ERRR////////////////////////////////////////////////////////////////\n";
					}
				}
				else 
				{
					$location = $location_ret;
				}
				
				echo "-----------------------------------------------------{$row[0]}----------------------------------------------------------------------\n";
			
				if(!$debug) update_post_meta($post_id, '_mpv_location', $location);
			}
			else
			{
				if(!$debug) delete_post_meta($post_id, '_mpv_location');
			}

			if(!$debug)
			{
				
				/*$pin_id = substr($row->icon, 4);
	
				$pin_id = intval(sprintf("%d", $pin_id));
	
				if($pin_id > 0)
				{
					update_post_meta($post_id, '_mpv_pin', $pin_id);
				}*/
				update_post_meta($post_id, '_mpv_pin', 12);
			
				delete_post_meta($post_id, '_mpv_inmap');
				delete_post_meta($post_id, '_mpv_in_img_map');
				add_post_meta($post_id, "_mpv_inmap", 1);
				
				foreach ($row as $key => $custom_field)
				{
					if(!in_array($key, $no_import))
					{
						update_post_meta($post_id, $names[$key], $custom_field);
					}
				}
			}

		}
		$row = fgetcsv( $file, 0, ';');
		$i++;
	} while ($row !== false );// && $i < 100));
	echo '</pre>';
	fclose ( $file );

	//echo '<pre>'.var_dump($rows).'</pre>';

}


function mapasdevista_admin_init() {
    
    global $pagenow;
    
    $mapinfo = get_option('mapasdevista', true);
    
    if( ($pagenow === "post.php" || $pagenow === "post-new.php" || (isset($_GET['page']) && $_GET['page'] === "mapasdevista_maps")) ) {
        // api do google maps versao 3 direto 
        /*$googleapikey = get_mapasdevista_theme_option('google_key');
        $googleapikey = $googleapikey ? "&key=$googleapikey" : '';
        wp_enqueue_script('google-maps-v3', 'http://maps.google.com/maps/api/js?sensor=false' . $googleapikey);

        
        wp_enqueue_script('openlayers', 'http://openlayers.org/api/OpenLayers.js');

        wp_enqueue_script('mapstraction', mapasdevista_get_baseurl() . '/js/mxn/mxn-min.js' );
        wp_enqueue_script('mapstraction-core', mapasdevista_get_baseurl() . '/js/mxn/mxn.core-min.js');
        wp_enqueue_script('mapstraction-googlev3', mapasdevista_get_baseurl() . '/js/mxn/mxn.googlev3.core-min.js');
        wp_enqueue_script('mapstraction-openlayers', mapasdevista_get_baseurl() . '/js/mxn/mxn.openlayers.core-min.js');*/
    	if(($pagenow === "post.php" || $pagenow === "post-new.php") && in_array(get_post_type(), $mapinfo['post_types']))
    	{
    		mapasdevista_enqueue_scripts($mapinfo);
    	}
    	elseif(isset($_GET['page']) && $_GET['page'] === "mapasdevista_maps")  
    	{
    		mapasdevista_enqueue_scripts($mapinfo);
    	}
    	
    }
    
    if (isset($_GET['page']) && $_GET['page'] === "mapasdevista_theme_page") {
        
        wp_enqueue_script('jcolorpicker', mapasdevista_get_baseurl() . '/admin/colorpicker/js/colorpicker.js', array('jquery') );
        wp_enqueue_style('colorpicker', mapasdevista_get_baseurl() . '/admin/colorpicker/css/colorpicker.css' );
        wp_enqueue_script('mapasdevista_theme_options', mapasdevista_get_baseurl() . '/admin/mapasdevista_theme_options.js', array('jquery', 'jcolorpicker') );
    
    }

    if($pagenow === "post.php" || $pagenow === "post-new.php")
    {
        wp_enqueue_script('metabox', mapasdevista_get_baseurl() . '/admin/metabox.js' );
        $data = array('options' => get_option('mapasdevista'));
        wp_localize_script('metabox', 'mapasdevista_options', $data);
    } elseif(isset($_GET['page']) && $_GET['page'] === 'mapasdevista_pins_page') {
        wp_enqueue_script('metabox', mapasdevista_get_baseurl() . '/admin/pins.js' );
    }


    wp_enqueue_style('mapasdevista-admin', mapasdevista_get_baseurl('template_directory') . '/admin/admin.css');
}

/* Page Template redirect */

function mapasdevista_regiser_post_type() {

    register_post_type('mapa', array(
        'labels' => array(
            'name' => 'Itens do Mapa',
            'singular_name' => 'Item do Mapa',
            'add_new' => 'Novo Item',
            'add_new_item' => 'Adicionar novo Item no mapa',
            'edit_item' => 'Editar',
            'new_item' => 'Novo item do mapa',
            'view_item' => 'Ver item do mapa',
            'search_items' => 'Search Buscar item do mapa',
            'not_found' => 'Nenhum Item no mapa',
            'not_found_in_trash' => 'Nenhum item do mapa na Lixeira',
            'parent_item_colon' => ''
        ),
        'public' => true,
        'rewrite' => array('slug' => 'mapa'),
        'capability_type' => 'post',
        'hierarchical' => false,
        'map_meta_cap ' => true,
        //'menu_position' => 6,
        'has_archive' => false, //se precisar de arquivo
        'supports' => array(
            'title',
            'editor',
            'post-formats',
            'thumbnail',
        	'custom-fields',
        ),
           
        )
    );
    
    // Add new taxonomy, make it hierarchical (like categories)
    $labels = array(
        'name' => 'Categorias',
        'singular_name' => 'Categoria',
        'search_items' =>  'Buscar categorias',
        'all_items' => 'Todas as categorias',
        'parent_item' => 'Categoria mãe',
        'parent_item_colon' => 'Categoria mãe:',
        'edit_item' => 'Editar categoria', 
        'update_item' => 'Atualizar categoria',
        'add_new_item' => 'Adicionar nova categoria',
        'new_item_name' => 'Nome da nova categoria',
        'menu_name' => 'Categorias',
    ); 	

    register_taxonomy('categoria-mapa',array('mapa'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'query_var' => true,
        //'rewrite' => false,
    ));

}

//add_post_type_support( 'mapa', array('post-formats', 'post-thumbnails') );

function mapasdevista_base_custom_query_vars($public_query_vars) {
    $public_query_vars[] = "mapa-tpl";

    return $public_query_vars;
}

// REDIRECIONAMENTOS
function mapasdevista_base_custom_url_rewrites($rules) {
    $new_rules = array(
        "mapa/?$" => "index.php?mapa-tpl=mapa",
    );

    return $new_rules + $rules;
}



function mapasdevista_page_template_redirect() {
    global $wp_query;
    
    $mapinfo = get_option('mapasdevista', true);


    
    if ($wp_query->get('mapa-tpl')  ) {
        if ( $mapinfo['visibility'] == 'public' || current_user_can('edit_posts')) {
            mapasdevista_get_template('template/main-template');
            exit;
        }
        else
            $wp_query->is_404 = true;
        
    }
}

add_filter('query_vars', 'mapasdevista_base_custom_query_vars');
add_filter('rewrite_rules_array', 'mapasdevista_base_custom_url_rewrites', 10, 1);
add_action('template_redirect', 'mapasdevista_page_template_redirect');

function mapasdevista_get_template($file, $context = null, $load = true) {
    
    $templates = array();
	if ( !is_null($context) )
		$templates[] = "{$file}-{$context}.php";

	$templates[] = "{$file}.php";
    
	$found = '';
	
    if (preg_match('|/wp-content/themes/|', __FILE__)) {
        $found = locate_template($templates, $load, false);
    } else {
        $f = is_null($context) || empty($context) || strlen($context) == 0 ? $file : $file . '-'. $context ;
        $file = $file . '.php';
        $f = $f . '.php';
        
        if (
            file_exists(TEMPLATEPATH . '/' . $f) ||
            file_exists(STYLESHEETPATH . '/' . $f) ||
            file_exists(TEMPLATEPATH . '/' . $file) ||
            file_exists(STYLESHEETPATH . '/' . $file) 
            ) {
            $found = locate_template($templates, $load, false);
        } else {
            $f = WP_CONTENT_DIR . '/plugins/mapasdevista/' . $f;
            if ($load)
                include $f;
            else
                $found = $f;
        }
            
    }
    
    return $found;
    
}

function mapasdevista_get_baseurl() {
    
    if (preg_match('|[\\\/]wp-content[\\\/]themes[\\\/]|', __FILE__))
        return get_bloginfo('template_directory') . '/';
    else
        return plugins_url('mapasdevista') . '/';
}


// COMMENTS

if (!function_exists('mapasdevista_comment')): 

function mapasdevista_comment($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;  
    ?>
    <li <?php comment_class("clearfix"); ?> id="comment-<?php comment_ID(); ?>">        

        <p class="comment-meta alignright bottom">
          <?php comment_reply_link(array('depth' => $depth, 'max_depth' => $args['max_depth'])) ?> <?php edit_comment_link( __('Edit', 'mapasdevista'), '| ', ''); ?>          
        </p>
        <div class="comment-entry clearfix">
            <div class="alignleft"><?php echo get_avatar($comment, 66); ?></div>
            <p class="comment-meta bottom">
              <?php printf( __('By <strong>%s</strong> on <strong>%s</strong> at <strong>%s</strong>.', 'mapasdevista'), get_comment_author_link(), get_comment_date(), get_comment_time()); ?>
              <?php if($comment->comment_approved == '0') : ?><br/><em><?php _e('Your comment is awaiting moderation.', 'mapasdevista'); ?></em><?php endif; ?>
            </p>
            <?php comment_text(); ?>
        </div>

    </li>
    <?php
}

endif; 


// IMAGES
function mapasdevista_get_image($name) {
    return mapasdevista_get_baseurl() . '/img/' . $name;
}

function mapasdevista_image($name, $params = null) {
    $extra = '';

    if(is_array($params)) {
        foreach($params as $param=>$value){
            $extra.= " $param=\"$value\" ";		
        }
    }

    echo '<img src="', mapasdevista_get_image($name), '" ', $extra ,' />';
}

add_action('comment_post_redirect', 'mapasdevista_handle_comments_ajax', 10, 2);

function mapasdevista_handle_comments_ajax($location, $comment) {
    
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        
        die(mapasdevista_get_post_ajax($comment->comment_post_ID));
        
    } else {
        
        return $location;
        
    }
}

function mapasdevista_create_homepage_map($args) {
    
    	/*
    	 if (get_option('mapasdevista_created_homepage'))
    		return __('You have done this before...', 'mapasdevista');
    	*/
    
    	$params = wp_parse_args(
    			$args,
    			array(
    					'name' => __('Home Page Map', 'mapasdevista'),
    					'api' => 'openlayers',
    					'type' => 'road',
    					'coord' => array(
    							'lat' => '-13.888513111069498',
    							'lng' => '-56.42951505224626'
    					),
    					'zoom' => '4',
    					'post_types' => array('post'),
    					'filters' => array('new'),
    					'taxonomies' => array('category')
    			)
    	);
    
    	$page = array(
    			'post_title' => 'Home Page',
    			'post_content' => __('Page automatically created by Mapas de Vista as a placeholder for your map.', 'mapasdevista'),
    			'post_status' => 'publish',
    			'post_type' => 'page'
    	);
    
    	$page_id = wp_insert_post($page);
    
    	if ($page_id) {
    		update_option('show_on_front', 'page');
    		update_option('page_on_front', $page_id);
    		update_option('page_for_posts', 0);
    
    		update_post_meta($page_id, '_mapasdevista', $params);
    
    		update_option('mapasdevista_created_homepage', true);
    
    		return true;
    
    	} else {
    		return $page_id;
    	}
   
}    
    
/**
 * 
 * @global WP_Query $MAPASDEVISTA_POSTS_RCACHE
 * @param int $page_id
 * @param array $mapinfo
 * @param array $postsArgs
 * @return WP_Query 
 */
function mapasdevista_get_posts($page_id, $mapinfo, $postsArgs = array()){
	ini_set("memory_limit", "2048M"); // TODO more clear and low memory way todo this
    global $MAPASDEVISTA_POSTS_RCACHE;
    
    if(is_object($MAPASDEVISTA_POSTS_RCACHE) && get_class($MAPASDEVISTA_POSTS_RCACHE) === 'WP_Query'){
        
        $MAPASDEVISTA_POSTS_RCACHE->rewind_posts();
        return $MAPASDEVISTA_POSTS_RCACHE;
    }else{
        
        if ($mapinfo['api'] == 'image') {
            
            $postsArgs += array(
                    'posts_per_page'     => -1,
                    'orderby'         => 'post_date',
                    'order'           => 'DESC',
                    'meta_key'        => '_mpv_in_img_map',
                    'meta_value'      => $page_id,
                    'post_type'       => $mapinfo['post_types'],
                    'ignore_sticky_posts' => true
                );


        } else {

            $postsArgs += array(
                        'posts_per_page'     => -1,
                        'orderby'         => 'post_date',
                        'order'           => 'DESC',
                        'meta_key'        => '_mpv_inmap',
                        'meta_value'      => $page_id,
                        'post_type'       => $mapinfo['post_types'],
                        'ignore_sticky_posts' => true
                    );
        }

        if (isset($_GET['mapasdevista_search']) && $_GET['mapasdevista_search'] != '')
            $postsArgs['s'] = $_GET['mapasdevista_search'];
        
        $MAPASDEVISTA_POSTS_RCACHE = new WP_Query($postsArgs); 
        
        return $MAPASDEVISTA_POSTS_RCACHE;
    }
}

add_filter('the_content', 'mapasdevista_gallery_filter');
function mapasdevista_gallery_filter($content){
    return str_replace('[gallery]', '[gallery link="file"]', $content);
}

function mapasdevista_view()
{
	?>
		<style type="text/css">
            <?php include( mapasdevista_get_template('template/style.css', null, false) ); ?>
        </style>
	<?php 

	include( mapasdevista_get_template('template/_init-vars', null, false) );

	include( mapasdevista_get_template('template/_load-js', null, false) );
	
	include( mapasdevista_get_template('template/_filter-menus', null, false) );
	
	//include( mapasdevista_get_template('template/_header', null, false) );
	?>
	<div id="post_overlay">
        <a id="close_post_overlay" title="Fechar"><?php mapasdevista_image("close.png", array("alt" => "Fechar")); ?></a>
        <div id="post_overlay_content" class="mapasdevista-fontcolor" >
		</div>
    </div>
		<div id="map">
	        
        </div>
	<?php
	
	include( mapasdevista_get_template('mapasdevista-loop', 'filter', false) );
	
	include( mapasdevista_get_template('mapasdevista-loop', 'bubble', false) );
	
	//include( mapasdevista_get_template('template/_filters', null, false) );
	
	//include( mapasdevista_get_template('template/_footer', null, false) );
}

function mapasdevista_view_filters($taxonomy = 'filter', $only = array())
{
	?>
		<div id="filters">
			<ul>
				<?php mapasdevista_view_taxonomy_checklist($taxonomy, 0, $only);?>
			</ul>
		</div>
		<script type="text/javascript">
		<!--
			function mapasdevista_uncheckall_filters()
			{
				jQuery('.taxonomy-filter-checkbox').removeAttr('checked');
			}
			jQuery(document).ready(function(){
				jQuery('.taxonomy-filter-checkbox').click(function() {
					var checked = jQuery(this).attr('checked');
					var id = jQuery(this)[0].id;
					
					mapasdevista_uncheckall_filters();
					mapstraction.removeAllFilters();
	
					var tax = jQuery(this).attr('name').replace('filter_by_', '').replace('[]', '');
		            var val = jQuery(this).val();
					
					if ( checked ) {
						jQuery("*[id*="+id+"]").attr('checked','checked');
						mapstraction.addFilter(tax, 'in', val);
					} else {
						jQuery("*[id*="+id+"]").removeAttr('checked');
		            }
					
		            mapstraction.doFilter();
		            updateResults();
				});
			});
		//-->
		</script>
	<?php
}

function mapasdevista_view_taxonomy_checklist($taxonomy, $parent = 0, $only = array())
{
	global $posts, $wpdb;

	$terms = array();
	$terms_ids = array();

	$posts_ids = $wpdb->get_col("SELECT post_id FROM $wpdb->postmeta WHERE meta_key ='_mpv_inmap' ");
	
	foreach($posts_ids as $post_id)
	{
		$_terms = get_the_terms($post_id, $taxonomy);

		if(is_array($_terms))
		{
			foreach($_terms as $_t)
			{
				if(!in_array($_t->term_id,$terms_ids) && $_t->parent == $parent)
				{
					$terms_ids[] = $_t->term_id;
					$key = $_t->name;
					$ikey = filter_var($_t->name, FILTER_SANITIZE_NUMBER_INT);
					if(intval($ikey) > 0)
					{
						$key = substr($ikey, 2).substr($ikey, 0, 2);// TODO arrumar um jeito de definir para datas
					}
					$terms[$key] = $_t;
				}
			}
		}
	}
	if (!is_array($terms) || ( is_array($terms) && sizeof($terms) < 1 ) ) return;
	
	$terms_keys = array_keys($terms);
	natcasesort($terms_keys);
	$terms_a = $terms;
	$terms = array();
	foreach ($terms_keys as $key)
	{
		if(count($only) == 0 || in_array($terms_a[$key]->slug, $only) )
		{
			$terms[] = $terms_a[$key];
		}
	}

	if($parent == 0 && count($only) == 0)
	{
		$tax = get_taxonomy($taxonomy); ?>
        <li class="filter-group-col"><h3><?php echo apply_filters('mapasdevista_filters_label', $tax->label); ?></h3><?php
	}
	/*elseif($parent == 0 && count($only) > 0)
	{
		$tax = get_
		<li class="filter-group-col"><h3><?php echo $tax->label; ?></h3><?php
	}*/
	if ($parent > 0): ?>
			<ul class='children'><?php
	endif;

	foreach ($terms as $term): ?>
				<li class="filter-group-col">
					<input type="checkbox" class="taxonomy-filter-checkbox" value="<?php echo $term->slug; ?>" name="filter_by_<?php echo $taxonomy; ?>[]" id="filter_by_<?php echo $taxonomy; ?>_<?php echo $term->slug; ?>" />
					<label for="filter_by_<?php echo $taxonomy; ?>_<?php echo $term->slug; ?>">
						<?php echo apply_filters('mapasdevista_filters_label',$term->name); ?>
					</label>
					<?php mapasdevista_view_taxonomy_checklist($taxonomy, $term->term_id); ?>
				</li><?php
	endforeach; 
	if ($parent > 0): ?>
			</ul><?php
	endif; ?>
		</li>
<?php
}

?>
