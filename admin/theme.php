<?php 

function get_mapasdevista_theme_default_options() {

    return array(
                        'bg_opacity' => 80,
                        'header_image' => '', //mapasdevista_get_baseurl() . '/img/mapas-de-vista.png',
                        'theme_color' => array(
                            'r' => 0,
                            'g' => 174,
                            'b' => 239
                        ),
                        'bg_color' => array(
                            'r' => 34,
                            'g' => 34,
                            'b' => 34
                        ),
    					'font_color' => array(
                            'r' => 255,
                            'g' => 255,
                            'b' => 255
                        ),
    					'form_bg_color' => array(
                        		'r' => 255,
                        		'g' => 255,
                        		'b' => 255
			    		),
    					'form_font_color' => array(
			    				'r' => 0,
			    				'g' => 0,
			    				'b' => 0
			    		),
    					'show_authors' => 'Y',
    					'show_dates' => 'Y',
    					'clusterer_enabled' => 'Y',
    					'markerSpiderfier_enabled' => 'Y'
                    );

}

function get_mapasdevista_theme_option($option_name) {
    $option = wp_parse_args( 
                    get_option('mapasdevista_theme_options'), 
                    get_mapasdevista_theme_default_options()
                );
    return isset($option[$option_name]) ? $option[$option_name] : false;
}

add_action('admin_init', 'mapasdevista_theme_options_init');

function mapasdevista_theme_options_init() {
    register_setting('mapasdevista_theme_options_options', 'mapasdevista_theme_options', 'mapasdevista_theme_options_validate_callback_function');
}

function mapasdevista_theme_options_validate_callback_function($input) {
    return $input;
}

function mapasdevista_theme_page() {

?>
  <div class="wrap span-20">
    <h2><?php echo __('Theme Options', 'mapasdevista'); ?></h2>

    <form action="options.php" method="post" class="clear prepend-top">
      <?php settings_fields('mapasdevista_theme_options_options'); ?>
      <?php 
      
      $options = wp_parse_args( 
                    get_option('mapasdevista_theme_options'), 
                    get_mapasdevista_theme_default_options()
                );
      
      
      ?>
      
      <div class="span-20 ">
      
        <h3><?php _e("Maps of View theme options"); ?></h3>
        
        <div class="span-6 last">
          
          <style>
          
          .colorpicker_box{
              width: 32px;
              height: 32px;
              border: 2px solid #CCC;
          }
          
          </style>
          
          <label for="header_image"><strong><?php _e("Header Image", "mapasdevista"); ?></strong></label><br/>
          <input type="text" id="header_image" class="text" name="mapasdevista_theme_options[header_image]" value="<?php echo htmlspecialchars($options['header_image']); ?>"/>
          <br/><br/>
          <label for="theme_color"><strong><?php _e("Theme color", "mapasdevista"); ?></strong></label><br/>
          <div id="theme_color_box" class="colorpicker_box"></div>
          <input type="hidden" id="theme_color_r" class="text" name="mapasdevista_theme_options[theme_color][r]" value="<?php echo htmlspecialchars($options['theme_color']['r']); ?>"/>
          <input type="hidden" id="theme_color_g" class="text" name="mapasdevista_theme_options[theme_color][g]" value="<?php echo htmlspecialchars($options['theme_color']['g']); ?>"/>
          <input type="hidden" id="theme_color_b" class="text" name="mapasdevista_theme_options[theme_color][b]" value="<?php echo htmlspecialchars($options['theme_color']['b']); ?>"/>
          <br/><br/>
          
          <label for="bg_color"><strong><?php _e("Background color", "mapasdevista"); ?></strong></label><br/>
          <div id="bg_color_box" class="colorpicker_box"></div>
          <input type="hidden" id="bg_color_r" class="text" name="mapasdevista_theme_options[bg_color][r]" value="<?php echo htmlspecialchars($options['bg_color']['r']); ?>"/>
          <input type="hidden" id="bg_color_g" class="text" name="mapasdevista_theme_options[bg_color][g]" value="<?php echo htmlspecialchars($options['bg_color']['g']); ?>"/>
          <input type="hidden" id="bg_color_b" class="text" name="mapasdevista_theme_options[bg_color][b]" value="<?php echo htmlspecialchars($options['bg_color']['b']); ?>"/>
          <br/><br/>
          
          <label for="bg_opacity"><strong><?php _e("Background opacity", "mapasdevista"); ?></strong></label><br/>
          <input type="text" id="bg_opacity" class="text" name="mapasdevista_theme_options[bg_opacity]" value="<?php echo htmlspecialchars($options['bg_opacity']); ?>"/>
          <br/><br/>
          
          <label for="font_color"><strong><?php _e("Font color", "mapasdevista"); ?></strong></label><br/>
          <div id="font_color_box" class="colorpicker_box"></div>
          <input type="hidden" id="font_color_r" class="text" name="mapasdevista_theme_options[font_color][r]" value="<?php echo htmlspecialchars($options['font_color']['r']); ?>"/>
          <input type="hidden" id="font_color_g" class="text" name="mapasdevista_theme_options[font_color][g]" value="<?php echo htmlspecialchars($options['font_color']['g']); ?>"/>
          <input type="hidden" id="font_color_b" class="text" name="mapasdevista_theme_options[font_color][b]" value="<?php echo htmlspecialchars($options['font_color']['b']); ?>"/>
          <br/><br/>

		  <label for="form_bg_color"><strong><?php _e("Form Background color", "mapasdevista"); ?></strong></label><br/>
          <div id="form_bg_color_box" class="colorpicker_box"></div>
          <input type="hidden" id="form_bg_color_r" class="text" name="mapasdevista_theme_options[form_bg_color][r]" value="<?php echo htmlspecialchars($options['form_bg_color']['r']); ?>"/>
          <input type="hidden" id="form_bg_color_g" class="text" name="mapasdevista_theme_options[form_bg_color][g]" value="<?php echo htmlspecialchars($options['form_bg_color']['g']); ?>"/>
          <input type="hidden" id="form_bg_color_b" class="text" name="mapasdevista_theme_options[form_bg_color][b]" value="<?php echo htmlspecialchars($options['form_bg_color']['b']); ?>"/>
          <br/><br/>
          
          <label for="form_font_color"><strong><?php _e("Form Font color", "mapasdevista"); ?></strong></label><br/>
          <div id="form_font_color_box" class="colorpicker_box"></div>
          <input type="hidden" id="form_font_color_r" class="text" name="mapasdevista_theme_options[form_font_color][r]" value="<?php echo htmlspecialchars($options['form_font_color']['r']); ?>"/>
          <input type="hidden" id="form_font_color_g" class="text" name="mapasdevista_theme_options[form_font_color][g]" value="<?php echo htmlspecialchars($options['form_font_color']['g']); ?>"/>
          <input type="hidden" id="form_font_color_b" class="text" name="mapasdevista_theme_options[form_font_color][b]" value="<?php echo htmlspecialchars($options['form_font_color']['b']); ?>"/>
          <br/><br/>

          <!--
          <label for="font_color"><strong><?php _e("Link color", "mapasdevista"); ?></strong></label><br/>
          <div id="link_color_box" class="colorpicker_box"></div>
          <input type="hidden" id="link_color_r" class="text" name="mapasdevista_theme_options[link_color][r]" value="<?php echo htmlspecialchars($options['link_color']['r']); ?>"/>
          <input type="hidden" id="link_color_g" class="text" name="mapasdevista_theme_options[link_color][g]" value="<?php echo htmlspecialchars($options['link_color']['g']); ?>"/>
          <input type="hidden" id="link_color_b" class="text" name="mapasdevista_theme_options[link_color][b]" value="<?php echo htmlspecialchars($options['link_color']['b']); ?>"/>
          <br/><br/>
          -->
          
          <label for="show_authors"><strong><?php _e("Show post authors?", "mapasdevista"); ?></strong></label>
          <label><input type="radio" id="show_authors_y" class="text" name="mapasdevista_theme_options[show_authors]" value="Y" <?php echo (!array_key_exists('show_authors', $options) || $options['show_authors'] == 'Y') ? 'checked="checked"' : ''; ?> /><?php _e('Yes', 'mapasdevista') ;?></label>
          <label><input type="radio" id="show_authors_n" class="text" name="mapasdevista_theme_options[show_authors]" value="N" <?php echo (array_key_exists('show_authors', $options) && $options['show_authors'] == 'N') ? 'checked="checked"' : ''; ?> /><?php _e('No', 'mapasdevista') ;?></label>
          <br/><br/>
          
          <label for="show_dates"><strong><?php _e("Show post created date?", "mapasdevista"); ?></strong></label>
          <label ><input type="radio" id="show_dates_y" class="text" name="mapasdevista_theme_options[show_dates]" value="Y" <?php echo (!array_key_exists('show_dates', $options) || $options['show_dates'] == 'Y') ? 'checked="checked"' : ''; ?> /><?php _e('Yes', 'mapasdevista') ;?></label>
          <label ><input type="radio" id="show_dates_n" class="text" name="mapasdevista_theme_options[show_dates]" value="N" <?php echo (array_key_exists('show_dates', $options) && $options['show_dates'] == 'N') ? 'checked="checked"' : ''; ?> /><?php _e('No', 'mapasdevista') ;?></label>
          <br/><br/>
          
          <label for="clusterer_enabled" class="clusterer-enabled-label"><strong><?php _e("Group markers as clusters?", "mapasdevista"); ?></strong></label><br/>
          <label class="clusterer-enabled-label"><span>
          	<input type="radio" id="clusterer_enabled_y" class="text" name="mapasdevista_theme_options[clusterer_enabled]" value="Y" <?php echo (!array_key_exists('clusterer_enabled', $options) || $options['clusterer_enabled'] == 'Y') ? 'checked="checked"' : ''; ?> /><?php _e('with automatic grouping of markers', 'mapasdevista') ;?><br/>
          	<img src="<?php echo mapasdevista_get_baseurl().'/img/comgrupos.png'; ?>" />
          </span></label><br/>
          <label class="clusterer-enabled-label"><span>
          	<input type="radio" id="clusterer_enabled_n" class="text" name="mapasdevista_theme_options[clusterer_enabled]" value="N" <?php echo (array_key_exists('clusterer_enabled', $options) && $options['clusterer_enabled'] == 'N') ? 'checked="checked"' : ''; ?> /><?php _e('without automatic grouping of markers', 'mapasdevista') ;?><br/>
          	<img src="<?php echo mapasdevista_get_baseurl().'/img/semgrupos.png'; ?>" />
          </span></label>
          
          <label for="markerSpiderfier_enabled" class="spiderfier-enabled-label"><strong><?php _e("Spiderfier overlapped markers?", "mapasdevista"); ?></strong></label><br/>
          <label class="spiderfier-enabled-label"><span>
          	<input type="radio" id="markerSpiderfier_enabled_y" class="text" name="mapasdevista_theme_options[markerSpiderfier_enabled]" value="Y" <?php echo (!array_key_exists('markerSpiderfier_enabled', $options) || $options['markerSpiderfier_enabled'] == 'Y') ? 'checked="checked"' : ''; ?> /><?php _e('with spiderfier overlapped markers', 'mapasdevista') ;?><br/>
          	<img src="<?php echo mapasdevista_get_baseurl().'/img/spiderfier.png'; ?>" />
          </span></label><br/>
          <label class="spiderfier-enabled-label"><span>
          	<input type="radio" id="markerSpiderfier_enabled_n" class="text" name="mapasdevista_theme_options[markerSpiderfier_enabled]" value="N" <?php echo (array_key_exists('markerSpiderfier_enabled', $options) && $options['markerSpiderfier_enabled'] == 'N') ? 'checked="checked"' : ''; ?> /><?php _e('without spiderfier overlapped markers', 'mapasdevista') ;?><br/>
          	<img src="<?php echo mapasdevista_get_baseurl().'/img/nospiderfier.png'; ?>" />
          </span></label>
          <br/><br/>

          
        </div>
      </div>
      <p class="textright clear prepend-top">
        <input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
      </p>
    </form>
  </div>
    
<?php    

}
