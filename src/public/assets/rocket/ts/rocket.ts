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
		
		(function () {
			$("form.rocket-impl-form").each(function () {
				rocket.impl.Form.scan($(this));
			});
			
			n2n.dispatch.registerCallback(function () {
				$("form.rocket-impl-form").each(function () {
					rocket.impl.Form.scan($(this));
				});
			});
		}) ();
	});
	
	export function contextOf(elem: HTMLElement): rocket.cmd.Context {
		return rocket.cmd.Context.findFrom($(elem));
	}
	
	export function handleErrorResponse(responseObject) {
		alert(JSON.stringify(responseObject));
		
		$("html").html(responseObject.responseText);
	}
}
