import { Message } from 'src/app/util/i18n/message';
import { UiContent } from '../ui-content';
import { Observable } from 'rxjs';
import { UiStructure } from '../ui-structure';
import { UiZoneError } from '../ui-zone-error';
import { UiStructureModelAdapter } from './ui-structure-model-adapter';
import { UiStructureModelMode } from '../ui-structure-model';

export class SimpleUiStructureModel extends UiStructureModelAdapter {

	public mode = UiStructureModelMode.NONE;
	public initCallback: (uiStructure: UiStructure) => void = () => {};
	public destroyCallback: () => void = () => {};
	public messagesCallback: () => Message[] = () => [];

	constructor(public content: UiContent|null = null) {
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

	get mainControlContents(): UiContent[] {
		return this.mainControlUiContents;
	}

	set mainControlContents(uiContents: UiContent[]) {
		this.mainControlUiContents = uiContents;
	}

	set asideContents(uiContents: UiContent[]) {
		this.asideUiContents = uiContents;
	}

	get asideContents(): UiContent[] {
		return this.asideUiContents;
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

	getMode(): UiStructureModelMode {
		return this.mode;
	}
}
