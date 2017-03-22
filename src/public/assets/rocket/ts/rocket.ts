namespace rocket {
	jQuery(document).ready(function ($) {
		var monitor: rocket.cmd.Monitor = new rocket.cmd.Monitor();

		monitor.scan($("#rocket-global-nav"));
	});
}