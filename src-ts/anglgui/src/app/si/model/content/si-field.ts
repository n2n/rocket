import { Message } from 'src/app/util/i18n/message';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { Observable } from 'rxjs';
import { SiGenericValue } from '../generic/si-generic-value';

export interface SiField {

	isDisplayable(): boolean;

	createUiStructureModel(compactMode: boolean): UiStructureModel;

	hasInput(): boolean;

	readInput(): object;

	// handleError(error: SiFieldError): void;

	// resetError(): void;

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

	// consume(consumableSiField: SiField): SiField;
}
