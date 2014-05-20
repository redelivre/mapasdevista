jQuery(document).ready(function()
{
	jQuery('#map').height(jQuery(window).height() -32);
	var map = L.map('map').setView([-14.307, -45.352], 4);
	L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
	}).addTo(map);
	
	var datapins =
    {
	        action: 'mapasdevista_get_posts_json',
    };
	
	jQuery.ajax(
    {
        type: 'POST',
        url: mapinfo.ajaxurl,
        data: datapins,
        success: function(data) {
            
        	L.geoJson(jQuery.parseJSON(data)).addTo(map);
            
        },
        beforeSend: function()
        {
        	//overlay_filtro();
        }, 
    });
	
});
        
