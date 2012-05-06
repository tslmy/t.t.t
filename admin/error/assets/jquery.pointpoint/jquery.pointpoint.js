/**
 * @name		jQuery PointPoint
 * @author		Martin Angelov
 * @version 	1.0
 * @url			http://tutorialzine.com/2011/09/jquery-pointpoint-plugin/
 * @license		MIT License
 */

(function($){

	// Defining our jQuery plugin

	$.fn.pointPoint = function(prop){

		// Default parameters
	    
		var options = $.extend({
			"class"		: "pointPointArrow",
			"distance"	: 30
		},prop);
		
		var pointers = [];
		
		// If CSS transforms are not supported, exit;
		
		if(!$.support.transform){
			this.destroyPointPoint = function(){};
			return this;
		}
		
		this.each(function(){
		
			var findMe = $(this),
				point = $('<div class="'+options['class']+'">').appendTo('body'),
				offset, center = {}, mouse = {}, props = {}, a, b, h, deg, op,
				pointHidden = true, rad_to_deg = 180/Math.PI;
			
			pointers.push(point);
		
			// Calculating the position of the pointer on mouse move
		
			$('html').bind('mousemove.pointPoint',function(e){
			
				if(pointHidden){
					point.show();
					pointHidden = false;
				}
				
				offset = findMe.offset();
				
				// The center of the element we are pointing at
				center.x = offset.left + findMe.outerWidth()/2;
				center.y = offset.top + findMe.outerHeight()/2;
				
				mouse.x = e.pageX;
				mouse.y = e.pageY;
				
				// We are treating the mouse position and center
				// point as the corners of a right triangle.
				// h is the hypotenuse, or distance between the two.
				
				a = mouse.y - center.y;
				b = center.x - mouse.x;
				h = Math.sqrt(a*a + b*b);
				
				// Calculating the degree (in radians),
				// the pointer should be rotated by:
				deg = Math.atan2(a,b);
				
				// Lowering the opacity of the pointer, depending
				// on the distance from the mouse pointer
				
				op = 1;				
				if(h < 50){
					op = 0;
				} else if(h < 160){
					op = (h - 50) / 110;
				}
				
				// Moving and rotating the pointer
				
				props.marginTop  = mouse.y-options.distance*Math.sin(deg);
				props.marginLeft = mouse.x+options.distance*Math.cos(deg);
				props.transform  = 'rotate('+(-deg*rad_to_deg)+'deg)';
				props.opacity    = op;
				
				point.css(props);

			}).bind('mouseleave.pointPoint',function(){
				point.hide();
				pointHidden = true;
			});

		});

		this.destroyPointPoint = function(){
		    
		    // Unbind all the event handlers
		    // and remove() the pointers 
		    
			$('html').unbind('.pointPoint');
			        
			$.each(pointers,function(){
				this.remove();
			});
		
		};
		
		return this;
	};
    
})(jQuery);