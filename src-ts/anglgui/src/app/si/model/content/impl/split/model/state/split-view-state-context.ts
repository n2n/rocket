import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SplitViewStateSubscription } from './split-view-state-subscription';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SplitViewMenuComponent } from '../../comp/split-view-menu/split-view-menu.component';
import { SplitViewMenuModel } from '../../comp/split-view-menu-model';
import { SplitOption } from '../split-option';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { SplitStyle } from '../split-context-si-field';
import { BehaviorSubject, Observable } from 'rxjs';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';

export class SplitViewStateContext implements SplitViewMenuModel {
	private toolbarUiStructure: UiStructure|null = null;
	private subscriptions: Array<SplitViewStateSubscription> = [];
	private optionMap = new Map<string, SplitOption>();
	private visibleKeys: string[] = [];
	private visibleKeysSubject = new BehaviorSubject<string[]>([]);

	constructor(readonly uiZone: UiZone, public splitStyle: SplitStyle) {
	}

	createSubscription(options: SplitOption[]): SplitViewStateSubscription {
		const subscription = new SplitViewStateSubscription(this, options);
		this.subscriptions.push(subscription);
		this.updateStructure();
		return subscription;
	}

	removeSubscription(subscription: SplitViewStateSubscription) {
		const i = this.subscriptions.indexOf(subscription);
		if (i === -1) {
			throw new Error('Subscription does not exist.');
		}

		this.subscriptions.splice(i, 1);
		this.updateStructure();
	}

	getSplitOptions(): SplitOption[] {
		return Array.from(this.optionMap.values());
	}

	getIconClass(): string|null {
		return this.splitStyle.iconClass;
	}

	getTooltip(): string|null {
		return this.splitStyle.tooltip;
	}

	// getVisibleKeys(): string[] {
	// 	return this.visibleKeys;
	// }

	getVisibleKeys$(): Observable<string[]> {
		return this.visibleKeysSubject.asObservable();
	}

	getVisibleKeysNum(): number {
		return this.visibleKeys.length;
	}

	containsVisibleKey(key: string) {
		return -1 < this.visibleKeys.indexOf(key);
	}

	addVisibleKey(key: string): void {
		const i = this.visibleKeys.indexOf(key);
		if (i > -1) {
			return;
		}

		this.visibleKeys.push(key);
		this.validateVisibleKeys(true);
	}

	removeVisibleKey(key: string): void {
		const i = this.visibleKeys.indexOf(key);
		if (i === -1) {
			return;
		}

		this.visibleKeys.splice(i, 1);
		this.validateVisibleKeys(true);
	}

	private validateVisibleKeys(triggerObsAnyway: boolean) {
		if (this.visibleKeys.length === 0 && this.optionMap.size > 0) {
			this.visibleKeys.push(this.optionMap.keys().next().value);
		} else if (!triggerObsAnyway) {
			return;
		}

		this.triggerVisibleKeysObs();
	}

	private triggerVisibleKeysObs() {
		this.visibleKeysSubject.next([...this.visibleKeys]);
	}

	private viewMenuUsm: UiStructureModel|null = null;

	private updateStructure() {
		const assigned = this.optionMap.size > 0;

		this.optionMap.clear();
		for (const subscription of this.subscriptions) {
			for (const splitOption of subscription.splitOptions) {
				this.optionMap.set(splitOption.key, splitOption);
			}
		}

		this.validateVisibleKeys(false);

		if (this.optionMap.size > 0) {
			if (!assigned) {
				this.uiZone.model.structure.addExtraToolbarStructureModel(this.viewMenuUsm = new SimpleUiStructureModel(
						new TypeUiContent(SplitViewMenuComponent, (ref) => {
							ref.instance.model = this;
						})));
			}

			return;
		}

		if (!assigned) {
			return;
		}

		this.uiZone.model.structure.removeExtraToolbarStructureModel(this.viewMenuUsm);
		this.viewMenuUsm = null;
	}
}
