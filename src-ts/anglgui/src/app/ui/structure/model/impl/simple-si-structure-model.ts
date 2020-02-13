import { UiStructureModel } from '../ui-structure-model';
import { Message } from 'src/app/util/i18n/message';
import { UiContent } from '../ui-content';
import { Observable, of } from 'rxjs';
import { UiStructure } from '../ui-structure';

export class SimpleUiStructureModel implements UiStructureModel {
	public disabled$: Observable<boolean>;
	public initCallback: (uiStructure: UiStructure) => void = () => {};
	public destroyCallback: () => void = () => {};
	public messagesCallback: () => Message[] = () => [];

	constructor(public content: UiContent|null = null, public asideContents: UiContent[] = []) {
	}

	init(uiStructure: UiStructure) {
		this.initCallback(uiStructure);
	}

	destroy() {
		this.destroyCallback();
	}

	getContent(): UiContent|null {
		return this.content;
	}

	getAsideContents(): UiContent[] {
		return this.asideContents;
	}

	getMessages(): Message[] {
		return this.messagesCallback();
	}

	getDisabled$(): Observable<boolean> {
		if (!this.disabled$) {
			this.disabled$ = of(false);
		}

		return this.disabled$;
	}
}
