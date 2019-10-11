import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { Message } from 'src/app/util/i18n/message';
import { SiEntryBuildup } from './si-entry-buildup';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';

export interface SiField {

	createUiStructureModel(): UiStructureModel;

	hasInput(): boolean;

	readInput(): object;

	handleError(error: SiFieldError): void;

	resetError(): void;

	getMessages(): Message[];

	isDisabled(): boolean;

	setDisabled(disabled: boolean): void;

	copy(entryBuildUp: SiEntryBuildup): SiField;
}
