namespace Rocket.Display {
	export class NavStore {
		private static readonly STORAGE_ITEM_NAME = "rocket_navigation_states";

		private _userId: number;
		private _scrollPos: number;
		private _navGroupOpenedIds: string[];

		constructor(userId: number, scrollPos: number, navGroupOpenedIds: string[]) {
			this._userId = userId;
			this._scrollPos = scrollPos;
			this.navGroupOpenedIds = navGroupOpenedIds;
		}

		public static read(userId: number): NavStore {
			let navStoreItem = JSON.parse(window.localStorage.getItem(NavStore.STORAGE_ITEM_NAME));
			return new NavStore(userId, navStoreItem.scrollPos, navStoreItem.navGroupOpenedIds);
		}

		public addOpenNavGroupId(id: string) {
			if (this.navGroupOpenedIds.indexOf(id) > -1) return;
			this.navGroupOpenedIds.push(id);
		}

		public removeOpenNavGroupId(id: string) {
			if (this.navGroupOpenedIds.indexOf(id) === -1) return;
			this.navGroupOpenedIds.splice(this.navGroupOpenedIds.indexOf(id), 1);
		}

		public save(): void {
			let jsonObj: Object = {"userId": this.userId, "scrollPos": this.scrollPos, "navGroupOpenedIds": this.navGroupOpenedIds};
			window.localStorage.setItem(NavStore.STORAGE_ITEM_NAME, JSON.stringify(jsonObj));
		}

		get userId() {
			return this._userId;
		}

		set userId(value) {
			this._userId = value;
		}

		get scrollPos() {
			return this._scrollPos;
		}

		set scrollPos(value) {
			this._scrollPos = value;
		}

		get navGroupOpenedIds() {
			return this._navGroupOpenedIds;
		}

		set navGroupOpenedIds(value) {
			this._navGroupOpenedIds = value;
		}
	}
}