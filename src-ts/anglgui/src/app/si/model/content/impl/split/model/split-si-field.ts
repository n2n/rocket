import { SiField } from '../../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SplitModel } from '../comp/split-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SplitOption } from './split-option';
import { SplitContextSiField, SplitStyle } from './split-context-si-field';
import { SiEntry } from '../../../si-entry';
import { SplitComponent } from '../comp/split/split.component';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { SimpleSiFieldAdapter } from '../../common/model/simple-si-field-adapter';
import { UiStructureModelMode, UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { SplitViewStateService } from './state/split-view-state.service';
import { SplitViewStateSubscription } from './state/split-view-state-subscription';
import { SiCrumb } from '../../meta/model/si-crumb';
import { ButtonControlUiContent } from 'src/app/si/model/control/impl/comp/button-control-ui-content';
import { ButtonControlModel } from 'src/app/si/model/control/impl/comp/button-control-model';
import { CrumbGroupComponent } from '../../meta/comp/crumb-group/crumb-group.component';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';

export class SplitSiField extends SiFieldAdapter {

	splitContext: SplitContextSiField|null;
	copyStyle: SplitStyle = { iconClass: null, tooltip: null };

	constructor(public refPropId: string, private viewStateService: SplitViewStateService) {
		super();
	}


// 	handleError(error: SiFieldError): void {
// 		console.log(error);
// 	}

	hasInput(): boolean {
		return false;
	}

	readInput(): object {
		throw new IllegalSiStateError('no input');
	}

	copy(): SiField {
		throw new Error('Method not implemented.');
	}

	// abstract copy(entryBuildUp: SiEntryBuildup): SiField;

	createUiStructureModel(): UiStructureModel {
		const uism = new SplitUiStructureModel(this.refPropId, this.splitContext, this.copyStyle, this.viewStateService);
		uism.messagesCallback = () => this.getMessages();
		return uism;
	}

	copyValue(): SiGenericValue {
		throw new Error('Not yet implemented');
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		throw new Error('Not yet implemented');
	}
}


class SplitUiStructureModel extends SimpleUiStructureModel implements SplitModel {

	private subscription: SplitViewStateSubscription;
	readonly childUiStructureMap = new Map<string, UiStructure>();
	private loadedKeys = new Array<string>();

	constructor(private refPropId: string, private splitContext: SplitContextSiField|null,
			private copyStyle: SplitStyle, private viewStateService: SplitViewStateService) {
		super();
	}

	getSplitStyle(): SplitStyle {
		return this.splitContext ? this.splitContext.style : { iconClass: null, tooltip: null };
	}

	getCopyTooltip(): string|null {
		return this.copyStyle.tooltip;
	}

	getSplitOptions(): SplitOption[] {
		if (this.splitContext) {
			return this.splitContext.getSplitOptions();
		}

		return [];
	}

	getLabelByKey(key: string) {
		return this.getSplitOptions().find(splitOption => splitOption.key === key).label;
	}

	getMode(): UiStructureModelMode {
		return UiStructureModelMode.ITEM_COLLECTION;
	}

	isKeyActive(key: string): boolean {
		return this.splitContext.isKeyActive(key);
	}

	activateKey(key: string) {
		this.splitContext.activateKey(key);
	}

	getChildUiStructureMap(): Map<string, UiStructure> {
		return this.childUiStructureMap;
	}

	getSiField$(key: string): Promise<SiField|null> {
		if (!this.splitContext) {
			throw new Error('No SplitContext assigned.');
		}

		return this.splitContext.getEntry$(key).then((entry: SiEntry|null) => {
			if (entry === null) {
				return null;
			}

			return entry.selectedEntryBuildup.getFieldById(this.refPropId);
		});
	}

	bind(uiStructure: UiStructure) {
		super.bind(uiStructure);

		this.content = new TypeUiContent(SplitComponent, (ref) => {
			ref.instance.model = this;
			// ref.instance.uiStructure = uiStructure;
		});

		this.subscription = this.viewStateService.subscribe(uiStructure, this.getSplitOptions(), this.getSplitStyle());

		for (const splitOption of this.getSplitOptions()) {
			const child = uiStructure.createChild(UiStructureType.ITEM, splitOption.shortLabel);
			this.childUiStructureMap.set(splitOption.key, child);
			child.visible = false;
			child.visible$.subscribe(() => {
				this.subscription.requestKeyVisibilityChange(splitOption.key, child.visible);
			});
		}

		this.checkChildUiStructureMap();
		this.subscription.visibleKeysChanged$.subscribe(() => {
			this.checkChildUiStructureMap();
		});
	}

	checkChildUiStructureMap() {
		for (const [key, childUiStructure] of this.childUiStructureMap) {
			childUiStructure.visible = this.subscription.isKeyVisible(key);

			if (!childUiStructure.visible || -1 < this.loadedKeys.indexOf(key) || !this.isKeyActive(key)) {
				continue;
			}

			this.loadedKeys.push(key);
			this.getSiField$(key).then((siField) => {
				if (!siField) {
					childUiStructure.model = this.createNotActiveUism();
					return;
				}

				childUiStructure.model = siField.createUiStructureModel();

				if (siField.hasInput() && siField.isGeneric()) {
					childUiStructure.createToolbarChild(new SimpleUiStructureModel(new ButtonControlUiContent(
							new SplitButtonControlModel(key, siField, this), childUiStructure.getZone())));
				}
			}).catch(() => {
				childUiStructure.model = this.createNotActiveUism();
			});
		}
	}

	private createNotActiveUism(): UiStructureModel {
		return new SimpleUiStructureModel(new TypeUiContent(CrumbGroupComponent, (ref) => {
			ref.instance.siCrumbGroup = {
				crumbs: [
					SiCrumb.createLabel('not active (todo: translate) ' /*this.translationService.translate('ei_impl_locale_not_active_label')*/)
				]
			};
		}));
	}

	unbind() {
		super.unbind();

		this.subscription.cancel();

		for (const childUiStructure of this.childUiStructureMap.values()) {
			childUiStructure.dispose();
		}
		this.childUiStructureMap.clear();
	}
}



class SplitButtonControlModel implements ButtonControlModel {
	private loading = false;

	private siButton: SiButton;
	private subSiButtons = new Map<string, SiButton>();

	constructor(private key: string, private siField: SiField, private model: SplitUiStructureModel) {
		this.siButton = new SiButton(null, 'btn btn-secondary', 'fas fa-reply-all');
		this.siButton.tooltip = this.model.getCopyTooltip();

		this.update();
	}

	update() {
		for (const splitOption of this.model.getSplitOptions()) {
			if (splitOption.key === this.key || this.subSiButtons.has(splitOption.key) || !this.model.isKeyActive(splitOption.key)) {
				continue;
			}

			this.subSiButtons.set(splitOption.key, new SiButton(splitOption.shortLabel, 'btn btn-secondary', 'fas fa-mail-forward'));
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
