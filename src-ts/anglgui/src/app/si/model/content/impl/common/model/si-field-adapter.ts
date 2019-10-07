import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { Message } from 'src/app/util/i18n/message';
import { SiEntryBuildup } from '../../../si-entry-buildup';
import { SiField } from '../../../si-field';
import { MessageFieldModel } from '../comp/message-field-model';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

export abstract class SiFieldAdapter implements SiField, MessageFieldModel {
	protected disabled = false;
	protected messages: Message[] = [];

	abstract hasInput(): boolean;

	abstract readInput(): object;

	isDisabled(): boolean {
		return this.disabled;
	}

	setDisabled(disabled: boolean) {
		this.disabled = disabled;
	}

	abstract copy(entryBuildUp: SiEntryBuildup): SiField;

	abstract createUiContent(): UiContent|null;

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
