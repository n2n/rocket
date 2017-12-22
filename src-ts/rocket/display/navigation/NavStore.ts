namespace Rocket.Display {
	export class NavStore {
		private static readonly STORAGE_ITEM_NAME = "rocket_navigation_states";

		private _userId: number;
		private _scrollPos: number;
		private _navGroupOpenedIds: string[];
		private _navStoreUserItems: NavStoreUserItem[] = [];

		constructor(userId: number, scrollPos: number, navGroupOpenedIds: string[], navStoreUserItems: NavStoreUserItem[]) {
			this._userId = userId;
			this._scrollPos = scrollPos;
			this._navStoreUserItems = navStoreUserItems;
			this.navGroupOpenedIds = navGroupOpenedIds;
		}

		public static read(userId: number): NavStore {
			let navStoreUserItems = JSON.parse(window.localStorage.getItem(NavStore.STORAGE_ITEM_NAME)) || [] ;

			let navStoreItem = navStoreUserItems.find((navStoreUserItem: NavStoreUserItem) => {
				return (navStoreUserItem.userId === userId);
			});

			if (!navStoreItem) {
				return new NavStore(userId, 0, [], navStoreUserItems);
			}

			return new NavStore(userId, navStoreItem.scrollPos, navStoreItem.navGroupOpenedIds, navStoreUserItems);
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
			let userItem = this.navStoreUserItems.find((userItem: NavStoreUserItem) => {
				if (userItem.userId === this.userId) {
					return true;
				}
			});

			if (!userItem) {
				userItem = {"userId": this.userId, "scrollPos": this.scrollPos, "navGroupOpenedIds": this.navGroupOpenedIds};
				this.navStoreUserItems.push(userItem);
			}

			userItem.scrollPos = this.scrollPos;
			userItem.navGroupOpenedIds = this.navGroupOpenedIds;

			window.localStorage.setItem(NavStore.STORAGE_ITEM_NAME, JSON.stringify(this.navStoreUserItems));
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

		get navStoreUserItems(): Rocket.Display.NavStoreUserItem[] {
			return this._navStoreUserItems;
		}

		set navStoreUserItems(value: Rocket.Display.NavStoreUserItem[]) {
			this._navStoreUserItems = value;
		}
	}

	export interface NavStoreUserItem {
		userId: number,
		scrollPos: number,
		navGroupOpenedIds: string[]
	}
}