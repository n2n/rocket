import { SimpleSiFieldAdapter } from './simple-si-field-adapter';
import { SiInputResetPoint } from '../../../si-input-reset-point';

export abstract class InSiFieldAdapter extends SimpleSiFieldAdapter {

	override hasInput(): boolean {
		return true;
	}

	abstract override readInput(): object;

	abstract override createInputResetPoint(): Promise<SiInputResetPoint>;

	// abstract copy(): SiField;

	// protected abstract createUiContent(uiStructure: UiStructure): UiContent;
}
