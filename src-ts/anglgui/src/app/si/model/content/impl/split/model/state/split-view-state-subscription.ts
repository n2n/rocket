import { SplitViewStateContext } from './split-view-state-context';
import { SplitOption } from '../split-option';

export class SplitViewStateSubscription {
	constructor(public splitViewStateContext: SplitViewStateContext, public splitOptions: SplitOption[]) {
	}

	isKeyVisible(key: string): boolean {
		return this.splitViewStateContext.containsVisibleKey(key);
	}

	requestKeyVisibilityChange(key: string, visible: boolean) {
		if (visible) {
			this.splitViewStateContext.addVisibleKey(key);
		} else {
			this.splitViewStateContext.removeVisibleKey(key);
		}
	}

	cancel() {
		this.splitViewStateContext.removeSubscription(this);
	}
}
