import { SiFieldAdapter } from './si-field-adapter';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

export abstract class OutSiFieldAdapter extends SiFieldAdapter {

	hasInput(): boolean {
		return false;
	}

	readInput(): object {
		throw new IllegalSiStateError('no input');
	}

	// abstract copy(entryBuildUp: SiEntryBuildup): SiField;

	protected abstract createUiContent(uiStructure: UiStructure): UiContent;
}
