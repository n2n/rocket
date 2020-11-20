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
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { ButtonControlUiContent } from 'src/app/si/model/control/impl/comp/button-control-ui-content';
import { ButtonControlModel } from 'src/app/si/model/control/impl/comp/button-control-model';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { SiField } from '../../../../si-field';

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
		this.subscription = this.viewStateService.subscribe(this.uiStructure, this.model.getSplitOptions(),
				this.model.getSplitStyle());

		for (const splitOption of this.model.getSplitOptions()) {
			const child = this.uiStructure.createChild(UiStructureType.ITEM, splitOption.shortLabel);
			this.childUiStructureMap.set(splitOption.key, child);
			child.visible = false;
			child.visible$.subscribe(() => {
				this.subscription.requestKeyVisibilityChange(splitOption.key, child.visible);
			});
		}
	}

	ngDoCheck() {
		for (const [key, childUiStructure] of this.childUiStructureMap) {
			childUiStructure.visible = this.subscription.isKeyVisible(key);

			if (!childUiStructure.visible || -1 < this.loadedKeys.indexOf(key) || !this.isKeyActive(key)) {
				continue;
			}

			this.loadedKeys.push(key);
			this.model.getSiField$(key).then((siField) => {
				if (!siField) {
					childUiStructure.model = this.createNotActiveUism();
					return;
				}

				childUiStructure.model = siField.createUiStructureModel() ;

				if (siField.hasInput() && siField.isGeneric()) {
					childUiStructure.createToolbarChild(new SimpleUiStructureModel(new ButtonControlUiContent(
							new SplitButtonControlModel(key, siField, this.model), childUiStructure.getZone())));
				}
			}).catch(() => {
				childUiStructure.model = this.createNotActiveUism();
			});
		}
	}

	ngOnDestroy() {
		this.subscription.cancel();

		for (const childUiStructure of this.childUiStructureMap.values()) {
			childUiStructure.dispose();
		}
	}

	isKeyActive(key: string): boolean {
		return this.model.isKeyActive(key);
	}

	activateKey(key: string) {
		this.model.activateKey(key);
	}

	getLabelByKey(key: string) {
		return this.model.getSplitOptions().find(splitOption => splitOption.key === key).label;
	}

	isKeyVisible(key: string): boolean {
		return this.subscription.isKeyVisible(key);
	}

	private createNotActiveUism(): UiStructureModel {
		return new SimpleUiStructureModel(new TypeUiContent(CrumbGroupComponent, (ref) => {
			ref.instance.siCrumbGroup = {
				crumbs: [
					SiCrumb.createLabel(this.translationService.translate('ei_impl_locale_not_active_label'))
				]
			};
		}));
	}

}

class SplitButtonControlModel implements ButtonControlModel {
	private loading = false;

	private siButton: SiButton;
	private subSiButtons = new Map<string, SiButton>();

	constructor(private key: string, private siField: SiField, private model: SplitModel) {
		this.siButton = new SiButton(null, 'btn btn-secondary', 'fa fa-reply-all');
		this.siButton.tooltip = this.model.getCopyTooltip();

		this.update();
	}

	update() {
		for (const splitOption of this.model.getSplitOptions()) {
			if (splitOption.key === this.key || this.subSiButtons.has(splitOption.key) || !this.model.isKeyActive(splitOption.key)) {
				continue;
			}

			this.subSiButtons.set(splitOption.key, new SiButton(splitOption.shortLabel, 'btn btn-secondary', 'fa fa-mail-forward'));
		}
	}

	isEmpty(): boolean {
		return this.subSiButtons.size === 0;
	}

	getSiButton(): SiButton {
		return this.siButton;
	}

	isLoading(): boolean {
		return this.loading;
	}

	isDisabled(): boolean {
		return this.loading;
	}

	exec(uiZone: UiZone, subKey: string|null): void {
		if (this.loading || !subKey) {
			return;
		}

		this.loading = true;

		this.model.getSiField$(subKey)
				.then((subSiField) => {
					if (this.siField.isGeneric() && subSiField.isGeneric()) {
						this.siField.pasteValue(subSiField.copyValue());
					}
				})
				.finally(() => { this.loading = false; });
	}

	getSubTooltip(): string|null {
		return this.model.getCopyTooltip();
	}

	getSubSiButtonMap(): Map<string, SiButton> {
		this.update();

		return this.subSiButtons;
	}
}
