import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { Message } from 'src/app/util/i18n/message';
import { SiEntryBuildup } from '../../../si-entry-buildup';
import { SiField } from '../../../si-field';
import { MessageFieldModel } from '../comp/message-field-model';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';

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

	getContextSiFields(): SiField[] {
		return [];
	}

	createUiStructureModel(): UiStructureModel {
		const model = new SimpleUiStructureModel(this.createUiContent());
		model.messagesCallback = () => this.getMessages();
		model.disabledCallback = () => this.isDisabled();
		return model;
	}

	protected abstract createUiContent(): UiContent;

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
