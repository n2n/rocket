
import { Message } from 'src/app/util/i18n/message';
import { UiContent } from './ui-content';

export interface UiStructureModel {

	getContent(): UiContent|null;

	getToolbarContents(): UiContent[];

	getMessages(): Message[];

	isDisabled(): boolean;
}
