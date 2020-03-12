import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { Message } from 'src/app/util/i18n/message';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { Observable } from 'rxjs';
import { SiGenericValue } from '../generic/si-generic-value';
import { GenericMissmatchError } from '../generic/generic-missmatch-error';
import { Fresult } from 'src/app/util/err/fresult';

export interface SiField {

	createUiStructureModel(): UiStructureModel;

	hasInput(): boolean;

	readInput(): object;

	handleError(error: SiFieldError): void;

	resetError(): void;

	getMessages(): Message[];

	isDisabled(): boolean;

	setDisabled(disabled: boolean): void;

	getDisabled$(): Observable<boolean>;

	// copy(entryBuildUp: SiEntryBuildup): SiField;

	isGeneric(): boolean;

	copyValue(): SiGenericValue;

	pasteValue(genericValue: SiGenericValue): Promise<void>;

	createResetPoint(): SiGenericValue;

	resetToPoint(genericValue: SiGenericValue): void;
}

