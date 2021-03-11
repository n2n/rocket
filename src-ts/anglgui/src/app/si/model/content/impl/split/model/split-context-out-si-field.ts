import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiEntryBuildup } from '../../../si-entry-buildup';
import { SplitContextSiField } from './split-context-si-field';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiField } from '../../../si-field';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { Observable, of } from 'rxjs';

export class SplitContextOutSiField extends SplitContextSiField {

	isDisplayable(): boolean {
		return false;
	}

	protected createUiContent(): UiContent {
		throw new IllegalSiStateError('SiField not displayable');
	}

	get activeKeys$(): Observable<string[]> {
		return of(this.getSplitOptions().map(so => so.key));
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

	deactivateKey(key: string) {
		throw new IllegalSiStateError('SplitContextOutSiField can not deactivate any keys.');
	}
}
