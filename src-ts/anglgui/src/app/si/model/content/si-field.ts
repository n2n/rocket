import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { SiContent } from 'src/app/si/model/structure/si-content';

export interface SiField {

	getContent(): SiContent|null;

	hasInput(): boolean;

	readInput(): object;

	handleError(error: SiFieldError): void;

	resetError(): void;

	copy(): SiField;
}
