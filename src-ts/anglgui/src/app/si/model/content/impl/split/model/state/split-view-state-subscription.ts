import { SplitViewStateContext } from './split-view-state-context';
import { SplitOption } from '../split-option';

export class SplitViewStateSubscription {

	constructor(public splitViewStateContext: SplitViewStateContext, public splitOptions: SplitOption[]) {

	}

	cancel() {
		this.splitViewStateContext.removeSubscription(this);
	}
}
