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
			let t = new Rocket.Impl.Translation.Translator(container);
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
			var navGroups: Rocket.Display.NavGroup[] = [];

			Jhtml.ready((elements) => {
				let elementsJq = $(elements);
				let rgn = elementsJq.find("#rocket-global-nav");
				if (rgn.length > 0) {
					nav.elemJq = rgn;
					let navGroupJq = rgn.find(".rocket-nav-group");
					navStore = Rocket.Display.NavStore.read(rgn.find("h2").data("rocketUserId"));
					navState = new Rocket.Display.NavState(navStore);

					navGroupJq.each((key: number, navGroupNode: Node) => {
						navGroups.push(Rocket.Display.NavGroup.build($(navGroupNode), navState));
					})

					rgn.scroll(() => {
						navStore.scrollPos = rgn.scrollTop();
						navStore.save();
					});

					var observer = new MutationObserver((mutations) => {
						nav.scrollToPos(navStore.scrollPos)

						mutations.forEach((mutation) => {
							navGroups.forEach((navGroup: NavGroup) => {
								if ($(Array.from(mutation.removedNodes)).get(0) === navGroup.elemJq.get(0)) {
									navState.offChanged(navGroup);
								}
							});

							navGroups.forEach((navGroup: NavGroup) => {
								if ($(Array.from(mutation.addedNodes)).get(0) === navGroup.elemJq.get(0)) {
									if (navState.isGroupOpen(navGroup.id)) {
										navGroup.open(0);
									} else {
										navGroup.close(0);
									}

									nav.scrollToPos(navStore.scrollPos);
								}
							});
						})
					})

					observer.observe(rgn.get(0), {childList: true});
				}

				elementsJq.each((key: number, node: Node) => {
					let nodeJq = $(node);
					if (nodeJq.hasClass("rocket-nav-group") && nodeJq.parent().get(0) === nav.elemJq.get(0)) {
						navGroups.push(Rocket.Display.NavGroup.build(nodeJq, navState));
					}
				});

				nav.scrollToPos(navStore.scrollPos);
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
