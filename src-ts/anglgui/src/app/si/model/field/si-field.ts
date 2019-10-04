import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { UiContent } from 'src/app/si/model/structure/ui-content';
import { Message } from 'src/app/util/i18n/message';
import { SiEntry } from '../entity/si-entry';

export interface SiField {

	createUiContent(): UiContent;

	hasInput(): boolean;

	readInput(): object;

	handleError(error: SiFieldError): void;

	resetError(): void;

	getMessages(): Message[];

	copy(siEntry: SiEntry): SiField;
}
