import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { Message } from 'src/app/util/i18n/message';
import { SiEntryBuildup } from '../../../si-entry-buildup';
import { SiField } from '../../../si-field';
import { MessageFieldModel } from '../comp/message-field-model';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { Observable, BehaviorSubject } from 'rxjs';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

export abstract class SiFieldAdapter implements SiField, MessageFieldModel {
	private disabledSubject = new BehaviorSubject<boolean>(false);
	protected messages: Message[] = [];

	abstract hasInput(): boolean;

	abstract readInput(): object;

	isDisabled(): boolean {
		return this.disabledSubject.getValue();
	}

	setDisabled(disabled: boolean) {
		this.disabledSubject.next(disabled);
	}

	getDisabled$(): Observable<boolean> {
		return this.disabledSubject;
	}

	abstract copy(entryBuildUp: SiEntryBuildup): SiField;

	getContextSiFields(): SiField[] {
		return [];
	}

	createUiStructureModel(): UiStructureModel {
		const model = new SimpleUiStructureModel(null);
		model.initCallback = (uiStructure) => { model.content = this.createUiContent(uiStructure); };
		model.messagesCallback = () => this.getMessages();
		model.disabled$ = this.disabledSubject;
		return model;
	}

	protected abstract createUiContent(uiStructure: UiStructure): UiContent;

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
