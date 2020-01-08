import { Component, OnInit, OnDestroy, DoCheck } from '@angular/core';
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
export class SplitComponent implements OnInit, OnDestroy, DoCheck {

	model: SplitModel;
	uiStructure: UiStructure;

	readonly childUiStructureMap = new Map<string, UiStructure>();

	private subscription: SplitViewStateSubscription;
	private loadedKeys = new Array<string>();

	constructor(private viewStateService: SplitViewStateService) {
	}

	ngOnInit() {
		this.subscription = this.viewStateService.subscribe(this.uiStructure, this.model.getSplitOptions(), this.model.getSplitStyle());

		for (const splitOption of this.model.getSplitOptions()) {
			const child = this.uiStructure.createContentChild(UiStructureType.ITEM, splitOption.shortLabel);
			this.childUiStructureMap.set(splitOption.key, child);
			child.visible = false;
			child.visible$.subscribe(() => {
				this.subscription.requestKeyVisibilityChange(splitOption.key, child.visible);
			});
		}
	}

	ngOnDestroy() {
		this.subscription.cancel();

		for (const childUiStructure of this.childUiStructureMap.values()) {
			childUiStructure.dispose();
		}
	}

	ngDoCheck() {
		for (const [key, childUiStructure] of this.childUiStructureMap) {
			childUiStructure.visible = this.subscription.isKeyVisible(key);

			if (!childUiStructure.visible || -1 < this.loadedKeys.indexOf(key)) {
				continue;
			}

			this.loadedKeys.push(key);
			this.model.getSiField$(key).subscribe((siField) => {
				childUiStructure.model = siField.createUiStructureModel();
			});
		}
	}

	isKeyVisible(key: string): boolean {
		return this.subscription.isKeyVisible(key);
	}


}
