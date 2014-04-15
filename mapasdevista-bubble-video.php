<?php

$content = get_the_content();

require_once( ABSPATH . WPINC . '/class-oembed.php' );

$oembed = _wp_oembed_get_object();


// gets the first video in the post
preg_match_all('|http://[^"\'\s]+|', $content, $m);
preg_match_all('|https://[^"\'\s]+|', $content, $m2); // TODO Need a better regex

$m[0] = array_merge($m[0], $m2[0]);

$video = false;

foreach ($m[0] as $match) {
    
    $found = false;
        
    foreach ($oembed->providers as $regexp => $data) {
    
        list( $providerurl, $is_regex ) = $data;
        
        if (!$is_regex)    
            $regexp = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $regexp ), '#' ) ) . '#i';
    
        if ( preg_match($regexp, $match) ) {
            $video = $match;
            $found = true;
            break;
        }   
    
    }
    
    if ($found)
        break;

}

if($video !== false)
	echo $oembed->get_html($video, 'maxwidth=270&width=270');

?>
