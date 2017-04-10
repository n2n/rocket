namespace rocket {
	jQuery(document).ready(function ($) {
		var jqContainer = $("#rocket-content-container");
		var container = new rocket.cmd.Container(jqContainer);
		var monitor: rocket.cmd.Monitor = new rocket.cmd.Monitor(container);

		monitor.scanMain($("#rocket-global-nav"), container.getMainLayer());
		monitor.scan(jqContainer);
		n2n.dispatch.registerCallback(function () {
			monitor.scan(jqContainer);
		});
		
		(function () {
			$(".rocket-impl-overview").each(function () {
				rocket.impl.OverviewContext.scan($(this));
			});
			
			n2n.dispatch.registerCallback(function () {
				$(".rocket-impl-overview").each(function () {
					rocket.impl.OverviewContext.scan($(this));
				});
			});
		}) ();
	});
	
	export function handleErrorResponse(responseObject) {
		alert(JSON.stringify(responseObject));
		
		$("html").html(responseObject.responseText);
	}
}
