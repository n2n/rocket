namespace Rocket {
	import Nav = Rocket.Display.Nav;
	import NavState = Rocket.Display.NavState;
	import NavGroup = Rocket.Display.NavGroup;
	let container: Rocket.Cmd.Container;
	let blocker: Rocket.Cmd.Blocker;
	let initializer: Rocket.Display.Initializer;
	let $ = jQuery;

	jQuery(document).ready(function ($) {
		var jqContainer = $("#rocket-content-container");

		container = new Rocket.Cmd.Container(jqContainer);

		blocker = new Rocket.Cmd.Blocker(container);
		blocker.init($("body"));

		initializer = new Rocket.Display.Initializer(container, jqContainer.data("error-tab-title"),
			jqContainer.data("display-error-label"));
		initializer.scan();

		Jhtml.ready(() => {
			initializer.scan();
		});

		(function () {
			Jhtml.ready(() => {
				$(".rocket-impl-overview").each(function () {
					Rocket.Impl.Overview.OverviewPage.from($(this));
				});
			});

			Jhtml.ready(() => {
				$(".rocket-impl-overview").each(function () {
					Rocket.Impl.Overview.OverviewPage.from($(this));
				});
			});
		})();

		(function () {
			$("form.rocket-form").each(function () {
				Rocket.Impl.Form.from($(this));
			});

			Jhtml.ready(() => {
				$("form.rocket-form").each(function () {
					Rocket.Impl.Form.from($(this));
				});
			});
		}) ();

		(function () {
			$(".rocket-impl-to-many").each(function () {
				Rocket.Impl.Relation.ToMany.from($(this));
			});

			Jhtml.ready(() => {
				$(".rocket-impl-to-many").each(function () {
					Rocket.Impl.Relation.ToMany.from($(this));
				});
			});
		}) ();

		(function () {
			$(".rocket-impl-to-one").each(function () {
				Rocket.Impl.Relation.ToOne.from($(this));
			});

			Jhtml.ready(() => {
				$(".rocket-impl-to-one").each(function () {
					Rocket.Impl.Relation.ToOne.from($(this));
				});
			});
		}) ();

		(function () {
			let t = new Rocket.Impl.Translator(container);
			t.scan();

			Jhtml.ready(() => {
				t.scan();
			});
		}) ();

		(function () {
			Jhtml.ready((elements) => {
				$(elements).find("a.rocket-jhtml").each(function () {
					new Rocket.Display.Command(Jhtml.Ui.Link.from(<HTMLAnchorElement> this)).observe();
				});
			});
		})();

		(function () {
			let moveState = new Impl.Order.MoveState();

			Jhtml.ready((elements) => {
				$(elements).find(".rocket-impl-insert-before").each(function () {
					new Impl.Order.Control($(this), Impl.Order.InsertMode.BEFORE, moveState);
				});
				$(elements).find(".rocket-impl-insert-after").each(function () {
					new Impl.Order.Control($(this), Impl.Order.InsertMode.AFTER, moveState);
				});
				$(elements).find(".rocket-impl-insert-as-child").each(function () {
					new Impl.Order.Control($(this), Impl.Order.InsertMode.CHILD, moveState);
				});
			});
		})();

		(function() {
			var nav: Rocket.Display.Nav = new Rocket.Display.Nav();
			var navStore: Rocket.Display.NavStore;
			var navState: Rocket.Display.NavState;

			Jhtml.ready((elements) => {
				let rgn = $(elements).find("#rocket-global-nav");
				if (rgn.length > 0) {
					nav.elemJq = rgn;
					elements = rgn.find(".rocket-nav-group");
					navStore = Rocket.Display.NavStore.read(rgn.find("h2").data("rocketUserId"));
					navState = new Rocket.Display.NavState(navStore);

					rgn.scroll(() => {
						navStore.scrollPos = rgn.scrollTop();
						navStore.save();
					})
				}

				nav.scrollToPos(navStore.scrollPos);

				for (let element of elements) {
					if (element.className.indexOf('rocket-nav-group') > -1
						&& element.parentElement === nav.elemJq.get(0)) {
						let navGroupElem = $(element)
						let navGroup = Rocket.Display.NavGroup.build(navGroupElem, navState);
						navState.onChanged(navGroup);
						navGroupElem.find("h3").click(() => {
							navGroup.toggle();
						});
					}
				}
			});
		})();
	});

	export function scan(context: Rocket.Cmd.Zone = null) {
		initializer.scan();
	}

	export function getContainer(): Rocket.Cmd.Container {
		return container;
	}

	export function layerOf(elem: HTMLElement): Rocket.Cmd.Layer {
		return Rocket.Cmd.Layer.of($(elem));
	}

	export function contextOf(elem: HTMLElement): Rocket.Cmd.Zone {
		return Rocket.Cmd.Zone.of($(elem));
	}

//	export function exec(url: string, config: Rocket.Cmd.ExecConfig = null) {
//		executor.exec(url, config);
//	}
}
