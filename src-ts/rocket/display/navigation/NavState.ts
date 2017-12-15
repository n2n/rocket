namespace Rocket.Display {
	export class NavState {
		private readonly LOCALSTORE_ITEM_PATTERN = "rocket_navigation_user_states";

		private _navGroups: NavGroup[];
		private _userId: number = null;
		private _scrollPos: number = null;
		private _activeNavItem: NavItem = null;
		private _openedNavGroups: NavGroup[] = [];
		private _localStoreManager: Rocket.util.LocalStoreManager = null;

		private navStateItems: NavStateItem[] = [];
		private navStateItem: NavStateItem = null;

		constructor(userId: number, navGroups: NavGroup[]) {
			this._userId = userId;
			this._navGroups = navGroups;
			this._localStoreManager = new Rocket.util.LocalStoreManager();
			this.init();
		}

		private init() {
			this.navStateItems = JSON.parse(this._localStoreManager.getItem(this.LOCALSTORE_ITEM_PATTERN)) || [];
			if (this.navStateItems === null
				|| !(this.navStateItem = this.navStateItems.find(navStateItem => navStateItem.userId === this._userId))) {
				this.navStateItem = this.buildNavStateItem();
				this.save();
				return;
			}

			this.scrollPos = this.navStateItem.scrollPos;

			for (let navGroupId of this.navStateItem.openedGroupIds) {
				let navGroup = this.navGroups.find(navGroup => navGroup.id === navGroupId);
				this.openedNavGroups.push(navGroup);
			}
		}

		public isGroupOpen(navGroup: NavGroup): boolean {
			return this.openedNavGroups.indexOf(navGroup) > -1;
		}

		public save(): void {
			this.navStateItems.splice(this.navStateItems.indexOf(this.navStateItem), 1);
			this.navStateItem = this.buildNavStateItem();
			this.navStateItems.unshift(this.navStateItem);
			this._localStoreManager.setItem(this.LOCALSTORE_ITEM_PATTERN, JSON.stringify(this.navStateItems));
		}

		private buildNavStateItem(): NavStateItem {
			let openedGroupIds: string[] = [];
			for (let navGroup of this.openedNavGroups) {
				openedGroupIds.push(navGroup.id);
			}

			return {userId: this._userId,
				scrollPos: this._scrollPos,
				activeNavItemUrlStr: null,
				openedGroupIds: openedGroupIds};
		}

		get navGroups(): Rocket.Display.NavGroup[] {
			return this._navGroups;
		}

		set navGroups(value: Rocket.Display.NavGroup[]) {
			this._navGroups = value;
		}

		get userId(): number {
			return this._userId;
		}

		set userId(value: number) {
			this._userId = value;
		}

		get scrollPos(): number {
			return this._scrollPos;
		}

		set scrollPos(value: number) {
			this._scrollPos = value;
		}

		get activeNavItem(): Rocket.Display.NavItem {
			return this._activeNavItem;
		}

		set activeNavItem(value: Rocket.Display.NavItem) {
			this._activeNavItem = value;
		}

		get openedNavGroups(): Rocket.Display.NavGroup[] {
			return this._openedNavGroups;
		}

		set openedNavGroups(value: Rocket.Display.NavGroup[]) {
			this._openedNavGroups = value;
		}
	}

	interface NavStateItem {
		userId: number,
		scrollPos: number,
		activeNavItemUrlStr: string,
		openedGroupIds: string[]
	};
}