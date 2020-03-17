import { Message } from 'src/app/util/i18n/message';
import { UiContent } from '../ui-content';
import { Observable } from 'rxjs';
import { UiStructure } from '../ui-structure';
import { UiZoneError } from '../ui-zone-error';
import { UiStructureModelAdapter } from './ui-structure-model-adapter';

export class SimpleUiStructureModel extends UiStructureModelAdapter {
	public initCallback: (uiStructure: UiStructure) => void = () => {};
	public destroyCallback: () => void = () => {};
	public messagesCallback: () => Message[] = () => [];

	constructor(public content: UiContent|null = null, public asideContents: UiContent[] = []) {
		super();
	}

	bind(uiStructure: UiStructure) {
		super.bind(uiStructure);
		this.initCallback(uiStructure);
	}

	unbind() {
		super.unbind();
		this.destroyCallback();
	}

	setDisabled$(disabled$: Observable<boolean>) {
		this.disabled$ = disabled$;
	}

	getContent(): UiContent|null {
		return this.content;
	}

	getAsideContents(): UiContent[] {
		return this.asideContents;
	}

	getZoneErrors(): UiZoneError[] {
		return this.messagesCallback().map(message => ({
			message,
			marked: (marked) => {
				this.reqBoundUiStructure().marked = marked;
			},
			focus: () => {
				this.reqBoundUiStructure().visible = true;
			}
		}));
	}
}
