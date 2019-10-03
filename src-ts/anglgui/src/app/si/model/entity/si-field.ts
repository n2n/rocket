import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { Message } from 'src/app/util/i18n/message';

export interface SiField {

	createContent(): SiContent;

	hasInput(): boolean;

	readInput(): object;

	handleError(error: SiFieldError): void;

	resetError(): void;

	getMessages(): Message[];

	copy(): SiField;
}
