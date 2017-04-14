namespace rocket {
	var container: rocket.cmd.Container;
	
	jQuery(document).ready(function ($) {
		var jqContainer = $("#rocket-content-container");
		container = new rocket.cmd.Container(jqContainer);
		var monitor: rocket.cmd.Monitor = new rocket.cmd.Monitor(new rocket.cmd.Executor(container));

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
	
	export function layerOf(elem: HTMLElement): rocket.cmd.Layer {
		return rocket.cmd.Layer.findFrom($(elem));
	}
	
	export function contextOf(elem: HTMLElement): rocket.cmd.Context {
		return rocket.cmd.Context.findFrom($(elem));
	}
	
	export function handleErrorResponse(url: string, responseObject: any) {
		container.handleError(url, responseObject.responseText);
	}
}
