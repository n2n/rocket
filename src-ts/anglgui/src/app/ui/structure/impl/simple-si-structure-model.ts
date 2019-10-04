import { SiStructureModel } from '../ui-structure-model';
import { SiControl } from '../../control/si-control';
import { UiContent } from '../si-content';
import { Message } from 'src/app/util/i18n/message';

export class SimpleSiStructureModel implements SiStructureModel {
	public controls: SiControl[] = [];
	public messagesCallback: () => Message[] = () => [];

	constructor(public content: UiContent|null = null) {
	}

	getContent(): UiContent|null {
		return this.content;
	}

	getControls(): SiControl[] {
		return this.controls;
	}

	getMessages(): Message[] {
		return this.messagesCallback();
	}
}
