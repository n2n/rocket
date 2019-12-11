import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SplitViewStateSubscription } from './split-view-state-subscription';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SplitViewMenuComponent } from '../../comp/split-view-menu/split-view-menu.component';
import { SplitViewMenuModel } from '../../comp/split-view-menu-model';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

export class SplitViewStateContext implements SplitViewMenuModel {
	private toolbarUiContent: UiContent;
	private subscriptions: Array<SplitViewStateSubscription>;
	private optionMap = new Map<string, string>();

	constructor(readonly uiStructure: UiStructure) {
		this.toolbarUiContent = new TypeUiContent(SplitViewMenuComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	createSubscription(options: Map<string, string>): SplitViewStateSubscription {
		const subscription = new SplitViewStateSubscription(this, options);
		this.subscriptions.push(subscription);
		return subscription;
	}

	removeSubscription(subscription: SplitViewStateSubscription) {
		const i = this.subscriptions.indexOf(subscription);
		if (i === -1) {
			throw new Error('Subscription does not exist.');
		}

		this.subscriptions.splice(i, 1);
	}

	getOptionMap(): Map<string, string> {
		return this.optionMap;
	}

	getIconClass(): string {
		throw new Error('Method not implemented.');
	}

	getTooltip(): string {
		throw new Error('Method not implemented.');
	}

	getVisibleKeys(): string[] {
		throw new Error('Method not implemented.');
	}

	setVisibleKeys(visibleKeys: string[]): void {
		throw new Error('Method not implemented.');
	}

	private updateStructure() {
		this.optionMap.clear();
		for (const subscription of this.subscriptions) {
			for (const [key, label] of subscription.optionMap) {
				this.optionMap.set(key, label);
			}
		}

		const i = this.uiStructure.toolbackUiContents.indexOf(this.toolbarUiContent);

		
		
	}
}
