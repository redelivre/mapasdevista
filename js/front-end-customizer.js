(function($){
    $(document).ready(function() {
        
        mxn.Marker.prototype._old_openBubble = mxn.Marker.prototype.openBubble;
        
        mxn.Marker.prototype.openBubble = function(){ 
            for (var ii = 0; ii < mapstraction.markers.length; ii ++) {
                mapstraction.markers[ii].closeBubble();
            }
            this._old_openBubble();
        }
        
        hWindow = $(window).height();
        
        // wp_localize_script não mantém o tipo do dado no javascript
        mapinfo.control_zoom = mapinfo.control_zoom != "false" ? mapinfo.control_zoom : false;
        mapinfo.control_pan = mapinfo.control_pan == "true";
        mapinfo.control_map_type = mapinfo.control_map_type == "true";

        mapstraction = new mxn.Mapstraction('map', mapinfo.api);

        if(mapinfo.api === 'image') {
            mapstraction.setImage(mapinfo.image_src);
            $(window).resize(function(e) {
                $("#map").css('height', $(window).height())
                .css('width', $(window).width());
            }).trigger('resize');
        } else if(mapinfo.api === 'googlev3') {
            mapstraction.maps[mapinfo.api].setOptions({
                mapTypeControl: mapinfo.control_map_type,
                panControl: mapinfo.control_pan,
                zoomControl: mapinfo.control_zoom != false,
                zoomControlOptions:{
                    style: mapinfo.control_zoom ? google.maps.ZoomControlStyle[mapinfo.control_zoom.toUpperCase()] : 0 ,
                    position: google.maps.ControlPosition.LEFT_CENTER
                },
                panControlOptions: {
                    position: google.maps.ControlPosition.LEFT_CENTER
                }
            });
        } else {
            mapstraction.addControls({
                pan: mapinfo.control_map_type,
                zoom: mapinfo.control_zoom,
                map_type: mapinfo.control_map_type
            });
        }

        mapstraction.setCenterAndZoom(new mxn.LatLonPoint(parseFloat(mapinfo.lat), parseFloat(mapinfo.lng)), parseInt(mapinfo.zoom));

        if (mapinfo.api == 'googlev3') {
            mapstraction.setMapType(mxn.Mapstraction[mapinfo.type.toUpperCase()]);
        }

        
        // Watch for zoom limit
        mapinfo.min_zoom = parseInt(mapinfo.min_zoom);
        if (mapinfo.min_zoom > 0) {
            mapstraction.changeZoom.addHandler(function() {
                if (mapstraction.getZoom() < mapinfo.min_zoom) {
                    mapstraction.setZoom(mapinfo.min_zoom);
                }
            });
        }
        mapinfo.max_zoom = parseInt(mapinfo.max_zoom);
        if (mapinfo.max_zoom > 0) {
            mapstraction.changeZoom.addHandler(function() {
                if (mapstraction.getZoom() > mapinfo.max_zoom) {
                    mapstraction.setZoom(mapinfo.max_zoom);
                }
            });
        }
        
        // Watch for pan limit 
        //mapstraction.setBounds( new mxn.BoundingBox( parseFloat(mapinfo.sw_lat), parseFloat(mapinfo.sw_lng), parseFloat(mapinfo.ne_lat), parseFloat(mapinfo.ne_lng) ) ); 
        //top
        mapinfo.ne_lat = parseFloat(mapinfo.ne_lat);
        mapinfo.ne_lng = parseFloat(mapinfo.ne_lng);
        mapinfo.sw_lat = parseFloat(mapinfo.sw_lat);
        mapinfo.sw_lng = parseFloat(mapinfo.sw_lng);
            
        if (mapinfo.sw_lat != 0 && mapinfo.sw_lng != 0 && mapinfo.ne_lat != 0 && mapinfo.ne_lng != 0) {
                
            mapstraction.endPan.addHandler(function() {
                var coord = mapstraction.getCenter();
                coord.lat = parseFloat(coord.lat);
                coord.lng = parseFloat(coord.lon);
                var lat;
                var lng;
                    
                lat = coord.lat < mapinfo.sw_lat ? mapinfo.sw_lat : coord.lat;
                if (lat == coord.lat) lat = coord.lat > mapinfo.ne_lat ? mapinfo.ne_lat : coord.lat;
                    
                lng = coord.lng < mapinfo.sw_lng ? mapinfo.sw_lng : coord.lon;
                if (lng == coord.lon) lng = coord.lon > mapinfo.ne_lng ? mapinfo.ne_lng : coord.lon;
                    
                if ( lat != coord.lat || lng != coord.lon) {
                    //console.log ('position changed');
                    mapstraction.setCenter(new mxn.LatLonPoint(lat, lng));
                    //mapasdevista.updateHash();
                }
                    
            });
            
        }
            

    });
    
    
})(jQuery);

