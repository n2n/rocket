import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SplitViewStateSubscription } from './split-view-state-subscription';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SplitViewMenuComponent } from '../../comp/split-view-menu/split-view-menu.component';
import { SplitViewMenuModel } from '../../comp/split-view-menu-model';
import { SplitOption } from '../split-option';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';

export class SplitViewStateContext implements SplitViewMenuModel {
	private toolbarUiStructure: UiStructure|null = null;
	private subscriptions: Array<SplitViewStateSubscription> = [];
	private optionMap = new Map<string, SplitOption>();
	private visibleKeys: string[] = [];

	constructor(readonly uiStructure: UiStructure) {
		
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

	getIconClass(): string {
		throw new Error('Method not implemented.');
	}

	getTooltip(): string {
		throw new Error('Method not implemented.');
	}

	getVisibleKeys(): string[] {
		return this.visibleKeys;
	}

	setVisibleKeys(visibleKeys: string[]): void {
		this.visibleKeys = visibleKeys;
	}

	private updateStructure() {
		const assigned = this.optionMap.size > 0;

		this.optionMap.clear();
		for (const subscription of this.subscriptions) {
			for (const splitOption of subscription.splitOptions) {
				this.optionMap.set(splitOption.key, splitOption);
			}
		}

		if (this.optionMap.size > 0) {
			if (!assigned) {
				this.toolbarUiStructure = this.uiStructure.createToolbarChild(new SimpleUiStructureModel(
						new TypeUiContent(SplitViewMenuComponent, (ref) => {
							ref.instance.model = this;
						})));
			}

			return;
		}

		if (!assigned) {
			return;
		}

		this.toolbarUiStructure.dispose();
	}
}
