import { SiField } from '../../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';
import { SplitModel } from '../comp/split-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SplitOption } from './split-option';
import { Observable } from 'rxjs';
import { SplitContextSiField, SplitStyle } from './split-context';
import { SiEntry } from '../../../si-entry';
import { map } from 'rxjs/operators';
import { SplitComponent } from '../comp/split/split.component';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

export class SplitSiField extends SiFieldAdapter implements SplitModel {
	splitContext: SplitContextSiField|null;

	constructor(public refPropId: string) {
		super();
	}

	hasInput(): boolean {
		throw new Error('Method not implemented.');
	}

	readInput(): object {
		throw new Error('Method not implemented.');
	}

	copy(): SiField {
		throw new Error('Method not implemented.');
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

	getContextSiFields(): SiField[] {
		return this.splitContext ? [this.splitContext] : [];
	}
}
