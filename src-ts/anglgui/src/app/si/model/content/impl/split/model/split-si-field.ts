import { SiField } from '../../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';

export class SplitSiField extends SiFieldAdapter {

	contextSiField: SiField|null;

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
		throw new Error('Method not implemented.');
	}
}
