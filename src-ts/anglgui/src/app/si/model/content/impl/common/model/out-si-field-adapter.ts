import { SiFieldAdapter } from './si-field-adapter';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiField } from '../../../si-field';
import { SiEntryBuildup } from '../../../si-entry-buildup';

export abstract class OutSiFieldAdapter extends SiFieldAdapter {

	hasInput(): boolean {
		return false;
	}

	readInput(): Map<string, string | number | boolean | File | null> {
		throw new IllegalSiStateError('no input');
	}

	abstract copy(entryBuildUp: SiEntryBuildup);
}
