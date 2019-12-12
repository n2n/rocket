import { SplitViewStateContext } from './split-view-state-context';

export class SplitViewStateSubscription {

	constructor(public splitViewStateContext: SplitViewStateContext, public optionMap: Map<string, string>) {

	}
}
