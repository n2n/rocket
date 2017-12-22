namespace Rocket.Display {
	export class NavState {
		private navStateListeners: NavStateListener[] = [];

		public onChanged(navStateListener: NavStateListener) {
			this.navStateListeners.push(navStateListener);
		}

		public change(id: string, opened: boolean) {

		}

		public isGroupOpen(navId: string): boolean {
			return false;
		}
	}

	export interface NavStateListener {
		changed(open: boolean): void;
	}
}