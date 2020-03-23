import { Injectable } from '@angular/core';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SplitViewStateContext } from './split-view-state-context';
import { SplitViewStateSubscription } from './split-view-state-subscription';
import { SplitOption } from '../split-option';
import { SplitStyle } from '../split-context-si-field';
import { skip } from 'rxjs/operators';

@Injectable({
	providedIn: 'root'
})
export class SplitViewStateService {
	private contexts = new Array<SplitViewStateContext>();

	constructor() {
	}

	subscribe(uiStructure: UiStructure, splitOptions: SplitOption[], splitStyle: SplitStyle): SplitViewStateSubscription {
		const context = this.getOrCreateContext(uiStructure.getRoot(), splitStyle);

		return context.createSubscription(splitOptions);
	}

	private getOrCreateContext(uiStructure: UiStructure, splitStyle: SplitStyle): SplitViewStateContext {
		let context = this.contexts.find((iContext) => {
			return iContext.uiStructure === uiStructure;
		});

		if (context) {
			return context;
		}

		context = new SplitViewStateContext(uiStructure, splitStyle);
		this.contexts.push(context);

		uiStructure.disposed$.pipe(skip(1)).subscribe(() => {
			if (uiStructure.disposed) {
				this.removeContext(context);
			}
		});

		return context;
	}

	private removeContext(context: SplitViewStateContext) {
		const i = this.contexts.indexOf(context);
		if (i === -1) {
			throw new Error('Unknown SplitViewStateContext.');
		}

		this.contexts.splice(i, 1);
	}
}
