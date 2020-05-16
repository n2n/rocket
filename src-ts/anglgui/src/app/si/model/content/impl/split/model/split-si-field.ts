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
import { UiStructureModelMode } from 'src/app/ui/structure/model/ui-structure-model';

export class SplitSiField extends SimpleSiFieldAdapter implements SplitModel {

	splitContext: SplitContextSiField|null;
	copyStyle: SplitStyle = { iconClass: null, tooltip: null };

	constructor(public refPropId: string) {
		super();
	}

	getCopyTooltip(): string|null {
		return this.copyStyle.tooltip;
	}

// 	handleError(error: SiFieldError): void {
// 		console.log(error);
// 	}

	hasInput(): boolean {
		return false;
	}

	readInput(): object {
		throw new Error('Method not implemented.');
	}

	copy(): SiField {
		throw new Error('Method not implemented.');
	}

	protected getMode(): UiStructureModelMode {
		return UiStructureModelMode.ITEM_COLLECTION;
	}

	protected createUiContent(uiStructure: UiStructure): UiContent {
		return new TypeUiContent(SplitComponent, (ref) => {
			ref.instance.model = this;
			ref.instance.uiStructure = uiStructure;
		});
	}

	getSplitStyle(): SplitStyle {
		return this.splitContext ? this.splitContext.style : { iconClass: null, tooltip: null };
	}

	getSplitOptions(): SplitOption[] {
		if (this.splitContext) {
			return this.splitContext.getSplitOptions();
		}

		return [];
	}

	isKeyActive(key: string): boolean {
		return this.splitContext.isKeyActive(key);
	}

	activateKey(key: string) {
		this.splitContext.activateKey(key);
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

	copyValue(): SiGenericValue {
		throw new Error('Not yet implemented');
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		throw new Error('Not yet implemented');
	}
}