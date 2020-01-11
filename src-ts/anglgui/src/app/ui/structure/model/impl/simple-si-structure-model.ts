import { UiStructureModel } from '../ui-structure-model';
import { Message } from 'src/app/util/i18n/message';
import { UiContent } from '../ui-content';
import { Observable, of } from 'rxjs';

export class SimpleUiStructureModel implements UiStructureModel {
	public disabled$: Observable<boolean>;
	public messagesCallback: () => Message[] = () => [];

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

	getDisabled$(): Observable<boolean> {
		if (!this.disabled$) {
			this.disabled$ = of(false);
		}

		return this.disabled$;
	}
}
