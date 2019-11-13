import { Subject, Subscription } from 'rxjs';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { UiContainer } from './ui-container';
import { UiZone } from './ui-zone';

export interface UiLayer {
	readonly container: UiContainer;
	readonly main: boolean;
	readonly currentZone: UiZone|null;
}

abstract class UiLayerAdapter implements UiLayer {
	protected zones: Array<UiZone> = [];
	protected currentZoneIndex: number|null = null;

	constructor(readonly container: UiContainer) {
	}

	readonly abstract main: boolean;

	get currentZone(): UiZone|null {
		if (this.currentZoneIndex === null) {
			return null;
		}

		if (this.zones[this.currentZoneIndex]) {
			return this.zones[this.currentZoneIndex];
		}

		throw new IllegalSiStateError('Layer contains invalid current zone');
	}

	protected getZoneById(id: number): UiZone|null {
		return this.zones.find(zone => zone.id === id) || null;
	}

	protected getZoneIndexById(id: number): number|null {
		return this.zones.findIndex(zone => zone.id === id) || null;
	}

	protected createZone(id: number, url: string|null): UiZone {
		if (!!this.getZoneById(id)) {
			throw new IllegalSiStateError('Zone with id ' + id + ' already exists. Url: ' + url);
		}

		if (this.currentZoneIndex !== null) {
			this.clearZoneAfterIndex(this.currentZoneIndex);
		}

		const zone = new UiZone(id, url, this as any);
		this.currentZoneIndex = this.zones.push(zone) - 1;
		zone.onDispose(() => {
			this.removeZone(zone);
		});
		return zone;
	}

	private clearZoneAfterIndex(currentZoneIndex: number) {
		for (const zone of this.zones.slice(currentZoneIndex + 1)) {
			zone.dispose();
		}
	}

	private removeZone(zone: UiZone) {
		const i = this.zones.indexOf(zone);
		if (i > -1) {
			this.zones.splice(i, 1);
			return;
		}

		throw new IllegalSiStateError('Zone to remove doesn\'t exist on layer.');
	}
}


export class MainUiLayer extends UiLayerAdapter {
	readonly main = true;

	constructor(container: UiContainer) {
		super(container);
	}

	pushZone(id: number, url: string): UiZone {
		return this.createZone(id, url);
	}

	popZone(id: number, verifyUrl: string): boolean {
		const index = this.getZoneIndexById(id);
		if (!index || this.zones[index].url !== verifyUrl) {
			// @todo temporary test to monitor angular routing behaviour
			throw new IllegalSiStateError('Zone pop url verify missmatch for id ' + id + ': '
					+ this.zones[index as number].url + ' != ' + verifyUrl);
// 				return false;
		}

		this.currentZoneIndex = index;
		return true;
	}
}

export class PopupUiLayer extends UiLayerAdapter {
	private disposeSubject = new Subject<void>();
	readonly main = false;

	constructor(container: UiContainer) {
		super(container);
	}

	pushZone(url: string|null): UiZone {
		return this.createZone(this.zones.length, url);
	}

	dispose() {
		for (const zone of this.zones) {
			zone.dispose();
		}

		this.disposeSubject.next();
		this.disposeSubject.complete();
	}

	get disposed() {
		return this.disposeSubject.closed;
	}

	onDispose(callback: () => any): Subscription {
		return this.disposeSubject.subscribe(callback);
	}
}