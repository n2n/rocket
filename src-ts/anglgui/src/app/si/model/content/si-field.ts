import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { Message } from 'src/app/util/i18n/message';
import { SiEntryBuildup } from './si-entry-buildup';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

export interface SiField {

	createUiContent(): UiContent;

	hasInput(): boolean;

	readInput(): object;

	handleError(error: SiFieldError): void;

	resetError(): void;

	getMessages(): Message[];

	isDisabled(): boolean;

	setDisabled(disabled: boolean);

	copy(entryBuildUp: SiEntryBuildup): SiField;
}
