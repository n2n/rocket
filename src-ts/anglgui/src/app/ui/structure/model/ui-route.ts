import { UiZone } from './ui-zone';
import { Subject, Subscription } from 'rxjs';

export class UiRoute {
	private disposeSubject = new Subject<void>();

	constructor(readonly id: number, readonly zone: UiZone) {
	}

	dispose() {
		this.disposeSubject.next();
		this.disposeSubject.complete();
	}

	onDispose(callback: () => any): Subscription {
		return this.disposeSubject.subscribe(callback);
	}
}
