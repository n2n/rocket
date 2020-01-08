import { Component, OnInit, OnDestroy, DoCheck } from '@angular/core';
import { SplitViewStateService } from '../../model/state/split-view-state.service';
import { SplitModel } from '../split-model';
import { SplitViewStateSubscription } from '../../model/state/split-view-state-subscription';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { CrumbGroupComponent } from '../../../meta/comp/crumb-group/crumb-group.component';
import { SiCrumb } from '../../../meta/model/si-crumb';
import { TranslationService } from 'src/app/util/i18n/translation.service';

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

	constructor(private viewStateService: SplitViewStateService, private translationService: TranslationService) {
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
				childUiStructure.model = siField ? siField.createUiStructureModel() : this.createNotActiveUism();
			});
		}
	}

	isKeyVisible(key: string): boolean {
		return this.subscription.isKeyVisible(key);
	}

	createNotActiveUism() {
		return new SimpleUiStructureModel(new TypeUiContent(CrumbGroupComponent, (ref) => {
			ref.instance.siCrumbGroup = { crumbs: [
				SiCrumb.createLabel(this.translationService.translate('ei_impl_locale_not_active_label'))
			] };
		}));
	}

}
