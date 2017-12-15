namespace Rocket.Display {
	export class Nav {
		private _htmlElement: HTMLElement;
		private _state: NavState;

		public constructor(htmlElement: HTMLElement, state: NavState) {
			this.htmlElement = htmlElement
			this.state = state;
		}

		public static setup(navJquery: Jquery): Nav {
			let navGroupJquery = $(".rocket-nav-group");

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

			return new Nav(navJquery.get(0), new NavState(navJquery.find("*[data-rocket-user-id]").data("rocket-user-id"), navGroups));
		}

		public initNavigation() {
			this.initGroups();
			this.setupEvents();
			this.scrollToPos(this.state.scrollPos);
		}

		private scrollToPos(scrollPos: number) {
			$(this.htmlElement).animate({
				scrollTop: scrollPos
			}, 0);
		}

		private initGroups() {
			for (let navGroup of this.state.navGroups) {
				if (!this.state.isGroupOpen(navGroup)) {
					navGroup.close(true);
				} else {
					navGroup.open(true);
				}
			}
		}

		private setupEvents() {
			let that = this;
			$(this.htmlElement).scroll(function(e) {
				let scrollPos = $(this).scrollTop();
				clearTimeout($.data(this, 'scrollTimer'));
				$.data(this, 'scrollTimer', setTimeout(function() {
					that.state.scrollPos = scrollPos;
					that.state.save();
				}, 150));
			});

			for (let navGroup of this.state.navGroups) {
				$(navGroup.titleHtmlElement).click(function () {
					let openedNavGroups = that.state.openedNavGroups;
					let openedNavGroupsArrPos = openedNavGroups.indexOf(navGroup);

					if (openedNavGroupsArrPos > -1) {
						navGroup.close();
						that.state.openedNavGroups.splice(openedNavGroupsArrPos, 1);
					} else {
						navGroup.open()
						that.state.openedNavGroups.push(navGroup);
					}

					that.state.scrollPos = $(that.htmlElement).scrollTop();
					that.state.save();
				});
			}
		}

		get state(): Rocket.Display.NavState {
			return this._state;
		}

		set state(value: Rocket.Display.NavState) {
			this._state = value;
		}

		get htmlElement(): HTMLElement {
			return this._htmlElement;
		}

		set htmlElement(value: HTMLElement) {
			this._htmlElement = value;
		}
	}
}