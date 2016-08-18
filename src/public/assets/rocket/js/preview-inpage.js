(function() {
	var trigger = function() {

		var elements = document.getElementsByClassName("rocket-preview-inpage-component");
		for (var i in elements) {
			if (elements[i].tagName != 'TEXTAREA') continue;
		
			var event = function() {
				this.style.height = this.scrollHeight + 'px';
			};

			addEvent(elements[i], 'keyup', event);
			addEvent(elements[i], 'keydown', event);
			addEvent(elements[i], 'click', event);
			addEvent(elements[i], 'change', event);
			
			elements[i].style.height = elements[i].scrollHeight + 'px';
		}
	};


	if (document.addEventListener) {
	    document.addEventListener("DOMContentLoaded", trigger, false);
	} else {
		window.addEvent("onload", trigger);
	}
	
	function addEvent( obj, type, fn ){
	   if (obj.addEventListener) {
	      obj.addEventListener( type, fn, false );
	   } else if (obj.attachEvent) {
	      obj["e"+type+fn] = fn;
	      obj[type+fn] = function() { obj["e"+type+fn]( window.event ); }
	      obj.attachEvent( "on"+type, obj[type+fn] );
	   }
	}
})();