
import { Message } from 'src/app/util/i18n/message';
import { UiContent } from './ui-content';
import { Observable } from 'rxjs';
import { UiStructure } from './ui-structure';

export interface UiStructureModel {

	init(uiStructure: UiStructure): void;

	destroy(): void;

	getContent(): UiContent|null;

	getAsideContents(): UiContent[];

	getMessages(): Message[];

	getDisabled$(): Observable<boolean>;
}
