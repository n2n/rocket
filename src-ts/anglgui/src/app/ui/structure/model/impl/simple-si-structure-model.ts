import { Message } from 'src/app/util/i18n/message';
import { UiContent } from '../ui-content';
import { Observable } from 'rxjs';
import { UiStructure } from '../ui-structure';
import { UiStructureModelAdapter } from './ui-structure-model-adapter';
import { UiStructureModelMode } from '../ui-structure-model';
import { BehaviorCollection } from 'src/app/util/collection/behavior-collection';
import { UiStructureError } from '../ui-structure-error';
import { map } from 'rxjs/operators';

export class SimpleUiStructureModel extends UiStructureModelAdapter {

	constructor(public content: UiContent|null = null) {
		super();
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

	public mode = UiStructureModelMode.NONE;
	public messagesCollection = new BehaviorCollection<Message>();
	public initCallback: (uiStructure: UiStructure) => void = () => {};
	public destroyCallback: () => void = () => {};

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

	getStructureErrors(): UiStructureError[] {
		return this.messagesCollection.get().map((m) => ({ message: m }));
	}

	getStructureErrors$(): Observable<UiStructureError[]> {
		return this.messagesCollection.get$().pipe(map((ms) => ms.map((m) => ({ message: m }))));
	}

	getMode(): UiStructureModelMode {
		return this.mode;
	}
}
