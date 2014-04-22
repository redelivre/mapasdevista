var mc; //global for debuging

jQuery(document).ready(function() {

	mc = new MarkerClusterer(mapstraction.getMap());
	mc.setIgnoreHidden(true);
	
	mapstraction.markerclusterer = mc;
	
});