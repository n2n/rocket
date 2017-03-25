namespace rocket {
	jQuery(document).ready(function ($) {
		var container = new rocket.cmd.Container($("#rocket-content-container"));
		var monitor: rocket.cmd.Monitor = new rocket.cmd.Monitor(container);

		monitor.scan($("#rocket-global-nav"), container.getMainLayer());
	});
}