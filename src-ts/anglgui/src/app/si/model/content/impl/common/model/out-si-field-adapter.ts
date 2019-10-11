import { SiFieldAdapter } from './si-field-adapter';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiEntryBuildup } from '../../../si-entry-buildup';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

export abstract class OutSiFieldAdapter extends SiFieldAdapter {

	hasInput(): boolean {
		return false;
	}

	readInput(): Map<string, string | number | boolean | File | null> {
		throw new IllegalSiStateError('no input');
	}

	abstract copy(entryBuildUp: SiEntryBuildup);

	protected abstract createUiContent(): UiContent;
}
