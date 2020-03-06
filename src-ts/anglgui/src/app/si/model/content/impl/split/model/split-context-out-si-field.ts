import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiEntryBuildup } from '../../../si-entry-buildup';
import { SplitContextSiField } from './split-context-si-field';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiField } from '../../../si-field';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { Fresult } from 'src/app/util/err/fresult';

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

	copy(entryBuildup: SiEntryBuildup): SiField {
		throw new Error('Method not implemented.');
	}

	isKeyActive(key: string): boolean {
		return true;
	}

	activateKey(key: string) {
		throw new IllegalSiStateError('SplitContextOutSiField can not activate any keys.');
	}

	readGenericValue(): SiGenericValue {
		throw new Error('Not yet implemented');
	}

	writeGenericValue(genericValue: SiGenericValue): Fresult<GenericMissmatchError, void> {
		throw new Error('Not yet implemented');
	}
}
