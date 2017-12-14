namespace Rocket.Display {
	export class NavState {
		private readonly LOCALSTORE_ITEM_PATTERN = "rocket_navigation_user_states";

		private _navGroups: NavGroup[];
		private _userId: number = null;
		private _scrollPos: number = null;
		private _activeNavItem: NavItem = null;
		private _openedNavGroups: NavGroup[] = null;
		private _localStoreManager: Rocket.util.LocalStoreManager = null;

		constructor(userId: number, navGroups: NavGroup[]) {
			this._userId = userId;
			this._navGroups = navGroups;
			this._localStoreManager = new Rocket.util.LocalStoreManager();
			this.init();
			this.setupClickEvents();
		}

		private init() {
			let navStateItems: Array<NavStateItem> = JSON.parse(this._localStoreManager.getItem(this.LOCALSTORE_ITEM_PATTERN));
			let navStateItem = null;
			if (navStateItems === null
				|| null === (navStateItem = navStateItems.find(navStateItem => navStateItem.userId === this._userId))) {
				navStateItems = [this.buildNavStateItem()];
				this._localStoreManager.setItem(this.LOCALSTORE_ITEM_PATTERN, JSON.stringify(navStateItems));
				return;
			}

			this.scrollPos = navStateItem.scrollPos;

		}

		public getActiveNavItem(): NavItem {
			return null;
		}

		private setupClickEvents() {
			for (let navGroup of this.navGroups) {
				$(navGroup.titleHtmlElement).click(function () {
					$(navGroup.navItemListHtmlElement).slideUp({duration: 'fast'});
				});
			}
		}

		private buildNavStateItem(): NavStateItem {
			return {userId: this._userId,
				scrollPos: this._scrollPos,
				activeNavItemUrlStr: null,
				openedGroupNames: null};
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
		openedGroupNames: string[]
	};
}