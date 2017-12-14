namespace Rocket.util {
	export class LocalStoreManager {
		getItem(name: string) {
			return window.localStorage.getItem(name);
		}

		setItem(name: string, value: string) {
			window.localStorage.setItem(name, value);
		}
	}
}