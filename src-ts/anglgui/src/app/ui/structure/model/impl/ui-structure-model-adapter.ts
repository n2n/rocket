import { UiStructureModel } from '../ui-structure-model';
import { UiStructure } from '../ui-structure';
import { UiContent } from '../ui-content';
import { Message } from 'src/app/util/i18n/message';
import { Observable, of } from 'rxjs';

export abstract class UiStructureModelAdapter implements UiStructureModel {
	protected content: UiContent|null = null;
	protected asideContents: UiContent[] =  [];
	protected messages: Message[] = [];
	private disabled$: Observable<boolean> = of(false);

	constructor() {
	}

	abstract init(uiStructure: UiStructure): void;

	abstract destroy(): void;

	getContent(): UiContent {
		if (this.content) {
			return this.content;
		}

		throw new Error('No UiContent available.');
	}

	getAsideContents(): UiContent[] {
		return this.asideContents;
	}

	getMessages(): Message[] {
		return this.messages;
	}

	getDisabled$(): Observable<boolean> {
		return this.disabled$;
	}
}
