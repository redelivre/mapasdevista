var mc; //global for debuging

jQuery(document).ready(function() {

	mc = new MarkerClusterer(mapstraction.getMap(), [], {imagePath : "/wp-content/plugins/mapasdevista/js/markerclustererplus/images/m"});
	mc.setIgnoreHidden(true);
	mc.setMaxZoom(15); // TODO create a option
	
	mapstraction.markerclusterer = mc;
	
});