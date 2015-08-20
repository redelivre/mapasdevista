var spiderConfig = {
    keepSpiderfied: true,
    event: 'mouseover'
};

var markerSpiderfier; //global for debuging

jQuery(document).ready(function() {
	
	markerSpiderfier = new OverlappingMarkerSpiderfier(mapstraction.getMap(), spiderConfig);
	
	mapstraction.markerSpiderfier = markerSpiderfier;
	
});
