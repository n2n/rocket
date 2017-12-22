namespace Rocket.Display {
	import NavState = Rocket.Display.NavState;

	export class NavGroup implements NavStateListener {
		private _id: string;
		private _elemJq: JQuery;
		private _navState: NavState;
		private _opened: boolean;

		public constructor(id: string, elemJq: JQuery, navState: NavState) {
			this.id = id;
			this.elemJq = elemJq;
			this.navState = navState;

			this.opened = navState.isGroupOpen(id);

			if (this.opened) {
				this.open(0);
			} else {
				this.close(0);
			}
		}

		public static build(elemJq: JQuery, navState: NavState) {
			let id = elemJq.data("navGroupId");
			let navGroup = new NavGroup(id, elemJq, navState);
			navState.onChanged(navGroup);
			elemJq.find("h3").click(() => {
				navGroup.toggle();
			});

			return navGroup;
		}

		public toggle() {
			if (this.opened) {
				this.close(150);
			} else {
				this.open(150);
			}

			this.navState.navStore.save();
		}

		public changed() {
			if (this.navState.isGroupOpen(this.id) === this.opened) return;
			this.opened = this.navState.isGroupOpen(this.id);

			if (this.opened === true) {
				this.open();
			}

			if (this.opened === false) {
				this.close();
			}
		}

		public open(ms: number = 150) {
			this.opened = true;
			let icon = this.elemJq.find("h3").find("i");

			icon.addClass("fa-minus");
			icon.removeClass("fa-plus");
			this.elemJq.find('.nav').stop(true, true).slideDown({duration: ms});
			this.navState.change(this.id, this.opened);
		}

		public close(ms: number = 150) {
			this.opened = false;
			let icon = this.elemJq.find("h3").find("i");

			icon.addClass("fa-plus");
			icon.removeClass("fa-minus");
			this.elemJq.find('.nav').stop(true, true).slideUp({duration: ms});

			this.navState.change(this.id, this.opened);
		}

		get navState(): Rocket.Display.NavState {
			return this._navState;
		}

		set navState(value: Rocket.Display.NavState) {
			this._navState = value;
		}

		get elemJq(): JQuery {
			return this._elemJq;
		}

		set elemJq(value: JQuery) {
			this._elemJq = value;
		}

		get id(): string {
			return this._id;
		}

		set id(value: string) {
			this._id = value;
		}

		get opened(): boolean {
			return this._opened;
		}

		set opened(value: boolean) {
			this._opened = value;
		}
	}
}