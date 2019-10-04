
import { SiControl } from 'src/app/si/model/control/si-control';
import { UiContent } from 'src/app/si/model/structure/ui-content';
import { Message } from 'src/app/util/i18n/message';

export interface SiStructureModel {

	getContent(): UiContent|null;

	getControls(): SiControl[];

	getMessages(): Message[];
}
