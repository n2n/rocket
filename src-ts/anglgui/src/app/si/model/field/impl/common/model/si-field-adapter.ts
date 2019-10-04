import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { SiField } from 'src/app/si/model/entity/si-field';
import { UiContent } from 'src/app/si/model/structure/ui-content';
import { MessageFieldModel } from 'src/app/ui/content/field/message-field-model';
import { Message } from 'src/app/util/i18n/message';

export abstract class SiFieldAdapter implements SiField, MessageFieldModel {
	protected messages: Message[] = [];

	abstract hasInput(): boolean;

	abstract readInput(): object;

	abstract copy(): SiField;

	abstract createContent(): UiContent|null;

	getMessages(): Message[] {
		return this.messages;
	}

	handleError(error: SiFieldError): void {
		this.messages.push(...error.getAllMessages());
	}

	resetError(): void {
		this.messages = [];
	}
}
