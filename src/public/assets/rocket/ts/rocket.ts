namespace rocket {
	jQuery(document).ready(function ($) {
		var monitor: rocket.cmd.Monitor = new rocket.cmd.Monitor(
				new rocket.cmd.Content($("#rocket-content-container")));

		monitor.scan($("#rocket-global-nav"));
	});
}