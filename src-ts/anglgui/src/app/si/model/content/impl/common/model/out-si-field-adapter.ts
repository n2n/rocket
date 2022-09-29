import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SimpleSiFieldAdapter } from './simple-si-field-adapter';
import { SiInputResetPoint } from '../../../si-input-reset-point';

export abstract class OutSiFieldAdapter extends SimpleSiFieldAdapter {

	override hasInput(): boolean {
		return false;
	}

	override readInput(): object {
		throw new IllegalSiStateError('no input');
	}

	createInputResetPoint(): Promise<SiInputResetPoint> {
		throw new IllegalSiStateError('no input');
	}

	// abstract copy(entryBuildUp: SiEntryBuildup): SiField;

}
