import { Component, OnInit, OnDestroy } from '@angular/core';
import { SplitViewStateService } from '../../model/state/split-view-state.service';
import { SplitModel } from '../split-model';
import { SplitViewStateSubscription } from '../../model/state/split-view-state-subscription';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';

@Component({
	selector: 'rocket-split',
	templateUrl: './split.component.html',
	styleUrls: ['./split.component.css']
})
export class SplitComponent implements OnInit, OnDestroy {

	model: SplitModel;
	uiStructure: UiStructure;

	private childUiStructureMap = new Map<string, UiStructure>();

	private subscription: SplitViewStateSubscription;

	constructor(private viewStateService: SplitViewStateService) {
	}

	ngOnInit() {
		this.subscription = this.viewStateService.subscribe(this.uiStructure, this.model.getSplitOptions());

		for (const splitOption of model.getSplitOptions()) {
			this.childUiStructureMap.set(splitOption.key,
					this.uiStructure.createChild(UiStructureType.ITEM, splitOption.shortLabel));
		}
		
	}

	ngOnDestroy() {
		this.subscription.cancel();
	}

	sfd(key: string) {
		this.subscription.;
	}

}
