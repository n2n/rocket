namespace Rocket.Display {
	export class Nav {
		private _state: NavState;

		public constructor(state: NavState) {
			this.state = state;
		}

		public static setup(navJquery: Jquery): Nav {
			let navGroupJquery = navJquery.find(".rocket-nav-group");

			let navGroups: NavGroup[] = [];
			navGroupJquery.each((key: number, htmlElem: HTMLElement) => {
				let jqueryElem = $(htmlElem);

				let navGroupTitleCollection = htmlElem.getElementsByClassName("rocket-global-nav-group-title");
				let titleElem = Array.prototype.slice.call(navGroupTitleCollection)[0];

				let navItems: NavItem[] = [];
				jqueryElem.find(".nav-item").each((key: number, navItemHtmlElem: HTMLElement) => {
					navItems.push(new NavItem(navItemHtmlElem));
				});

				navGroups.push(new Rocket.Display.NavGroup(jqueryElem.find("ul").get(0), titleElem, navItems));
			})

			return new Nav(new NavState(navJquery.find("*[data-rocket-user-id]").data("rocket-user-id"), navGroups));
		}

		public initNavigation() {
			this.state.getActiveNavItem();
		}

		get state(): Rocket.Display.NavState {
			return this._state;
		}

		set state(value: Rocket.Display.NavState) {
			this._state = value;
		}
	}
}