import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiEntryBuildup } from '../../../si-entry-buildup';
import { SplitContextSiField } from './split-context';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiField } from '../../../si-field';

export class SplitContextOutSiField extends SplitContextSiField {

	protected createUiContent(): UiContent {
		throw new Error('Method not implemented.');
	}

	hasInput(): boolean {
		return false;
	}

	readInput(): object {
		throw new IllegalSiStateError('No input available.');
	}

	copy(entryBuildUp: SiEntryBuildup): SiField {
		throw new Error('Method not implemented.');
	}
}
