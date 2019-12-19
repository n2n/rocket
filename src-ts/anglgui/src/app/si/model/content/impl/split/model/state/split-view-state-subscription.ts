import { SplitViewStateContext } from './split-view-state-context';
import { SplitOption } from '../split-option';

export class SplitViewStateSubscription {
	constructor(public splitViewStateContext: SplitViewStateContext, public splitOptions: SplitOption[]) {
	}

	isKeyVisible(key: string): boolean {
		return -1 < this.splitViewStateContext.getVisibleKeys().indexOf(key);
	}

	cancel() {
		this.splitViewStateContext.removeSubscription(this);
	}
}
