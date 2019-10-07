
import { Message } from 'src/app/util/i18n/message';
import { UiContent } from './ui-content';

export interface UiStructureModel {

	getContent(): UiContent|null;

	getControls(): UiContent[];

	getMessages(): Message[];

	isDisabled(): boolean;
}
