import { UiStructureModel } from '../ui-structure-model';
import { Message } from 'src/app/util/i18n/message';
import { UiContent } from '../ui-content';

export class SimpleUiStructureModel implements UiStructureModel {
	public messagesCallback: () => Message[] = () => [];
	public disabledCallback: () => boolean = () => false;

	constructor(public content: UiContent|null = null, public controls: UiContent[] = []) {
	}

	getContent(): UiContent|null {
		return this.content;
	}

	getAsideContents(): UiContent[] {
		return this.controls;
	}

	getMessages(): Message[] {
		return this.messagesCallback();
	}

	isDisabled(): boolean {
		return this.disabledCallback();
	}
}
