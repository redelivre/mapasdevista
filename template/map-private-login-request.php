<?php get_header(); 
echo '<h2>';
echo __('É necessário estar logado para visualizar essa página! Faça o seu login ', 'mapasdevista').
'<a href="'.wp_login_url(home_url('/mapa')).'">'.__('aqui', 'mapasdevista');
echo '</h2><br/><br/>';
get_footer(); ?>