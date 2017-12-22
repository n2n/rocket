namespace Rocket.Display {
	export class Nav {
		private _elemJq: JQuery;
		private _state: NavState;

		public init(elemJq: JQuery) {
			this.elemJq = elemJq;
		}

		public initNavigation(navGroupsJQuery: JQuery) {
			this.state.initNavGroups(navGroupsJQuery);
			this.initGroups();
			this.scrollToPos(this.state.scrollPos);
			this.setupEvents();
		}

		private scrollToPos(scrollPos: number) {
			this.elemJq.animate({
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

		}

		get state(): Rocket.Display.NavState {
			return this._state;
		}

		set state(value: Rocket.Display.NavState) {
			this._state = value;
		}

		get elemJq(): JQuery {
			return this._elemJq;
		}

		set elemJq(value: JQuery) {
			this._elemJq = value;
		}
	}
}