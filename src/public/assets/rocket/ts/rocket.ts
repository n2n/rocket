namespace rocket {
	jQuery(document).ready(function ($) {
		var monitor: rocket.cmd.Monitor = new rocket.cmd.Monitor($(".rocket-content-container"));
		
		monitor.asdf();
		
		var elem1 = document.getElementById("rocket-conf-nav");
		var elem2 = document.getElementById("rocket-content-container");
		
		$([elem1, elem2]).css({"background": "blue"});
		
	});
}