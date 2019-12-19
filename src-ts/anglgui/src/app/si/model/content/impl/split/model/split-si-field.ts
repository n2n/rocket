import { SiField } from '../../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';
import { SplitModel } from '../comp/split-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SplitOption } from './split-option';
import { Observable } from 'rxjs';
import { SplitContextSiField } from './split-context';
import { SiEntry } from '../../../si-entry';
import { map } from 'rxjs/operators';
import { SplitComponent } from '../comp/split/split.component';

export class SplitSiField extends SiFieldAdapter implements SplitModel {
	splitContext: SplitContextSiField|null;

	constructor(public relFieldId: string) {
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

	protected createUiContent(): UiContent {
		return new TypeUiContent(SplitComponent, (ref, uiStructure) => {
			ref.instance.model = this;
			ref.instance.uiStructure = uiStructure;
		});
	}

	getSplitOptions(): SplitOption[] {
		if (this.splitContext) {
			return this.splitContext.getSplitOptions();
		}

		return [];
	}

	getSiField$(key: string): Observable<SiField> {
		if (!this.splitContext) {
			throw new Error('Method not implemented.');
		}

		return this.splitContext.getEntry$(key).pipe(map((entry: SiEntry) => {
			return entry.selectedEntryBuildup.getFieldById(this.relFieldId);
		}));
	}
}
