namespace Rocket.Display {
	export class NavState {
		private navStateListeners: NavStateListener[] = [];
		private _navStore: NavStore;

		constructor(navStore: NavStore) {
			this._navStore = navStore;
		}

		public onChanged(navStateListener: NavStateListener) {
			let index = this.navStateListeners.indexOf(navStateListener);
			if (index > -1) {
				//console.log("DUPLICATE" + index);
			}
			this.navStateListeners.push(navStateListener);
		}

		public offChanged(navStateListener: NavStateListener) {
			this.navStateListeners.splice(this.navStateListeners.indexOf(navStateListener), 1);
		}

		public change(id: string, opened: boolean) {
			if (opened) {
				this.navStore.addOpenNavGroupId(id);
			} else {
				this.navStore.removeOpenNavGroupId(id);
			}

			this.navStore.save();

			//console.log("listeners: " + this.navStateListeners.length);

			this.navStateListeners.forEach((navStateListener: NavStateListener) => {
				navStateListener.changed(opened);
			})
		}

		public isGroupOpen(navId: string): boolean {
			return !!this.navStore.navGroupOpenedIds.find((id: string) => { return id == navId });
		}


		get navStore(): Rocket.Display.NavStore {
			return this._navStore;
		}

		set navStore(value: Rocket.Display.NavStore) {
			this._navStore = value;
		}
	}

	export interface NavStateListener {
		changed(open: boolean): void;
	}
}