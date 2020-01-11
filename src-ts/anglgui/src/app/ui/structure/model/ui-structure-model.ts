
import { Message } from 'src/app/util/i18n/message';
import { UiContent } from './ui-content';
import { Observable } from 'rxjs';

export interface UiStructureModel {

	getContent(): UiContent|null;

	getAsideContents(): UiContent[];

	getMessages(): Message[];

	getDisabled$(): Observable<boolean>;
}
