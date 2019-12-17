import { Injectable } from '@angular/core';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SplitViewStateContext } from './split-view-state-context';
import { SplitViewStateSubscription } from './split-view-state-subscription';
import { SplitOption } from '../split-option';

@Injectable({
	providedIn: 'root'
})
export class SplitViewStateService {
	private contexts = new Array<SplitViewStateContext>();

	constructor() {
	}

	subscribe(uiStructure: UiStructure, splitOptions: SplitOption[]): SplitViewStateSubscription {
		const context = this.getOrCreateContext(uiStructure.getRoot());

		return context.createSubscription(splitOptions);
	}

	private getOrCreateContext(uiStructure: UiStructure): SplitViewStateContext {
		let context = this.contexts.find((iContext) => {
			return iContext.uiStructure === uiStructure;
		});

		if (context) {
			return context;
		}

		context = new SplitViewStateContext(uiStructure);
		this.contexts.push(context);
		return context;
	}
}
