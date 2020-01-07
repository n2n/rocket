
import { Message } from 'src/app/util/i18n/message';
import { UiContent } from './ui-content';
import { UiStructure } from './ui-structure';

export interface UiStructureModel {

	getContent(): UiContent|null;

	getAsideContents(): UiContent[];

	getMessages(): Message[];

	isDisabled(): boolean;
}
