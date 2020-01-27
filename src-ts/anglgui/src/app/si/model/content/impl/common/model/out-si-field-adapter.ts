import { SiFieldAdapter } from './si-field-adapter';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiEntryBuildup } from '../../../si-entry-buildup';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiField } from '../../../si-field';

export abstract class OutSiFieldAdapter extends SiFieldAdapter {

	hasInput(): boolean {
		return false;
	}

	readInput(): object {
		throw new IllegalSiStateError('no input');
	}

	abstract copy(entryBuildUp: SiEntryBuildup): SiField;

	protected abstract createUiContent(uiStructure: UiStructure): UiContent;
}
