import { SiStructureModel } from '../si-structure-model';
import { SiControl } from '../../control/si-control';
import { SiContent } from '../si-content';
import { Message } from 'src/app/util/i18n/message';

export class SimpleSiStructureModel implements SiStructureModel {
	public controls: SiControl[] = [];
	public messagesCallback: () => Message[] = () => [];

	constructor(public content: SiContent|null = null) {
	}

	getContent(): SiContent|null {
		return this.content;
	}

	getControls(): SiControl[] {
		return this.controls;
	}

	getMessages(): Message[] {
		return this.messagesCallback();
	}
}
