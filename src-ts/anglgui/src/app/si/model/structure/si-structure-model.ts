
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { Message } from 'src/app/util/i18n/message';

export interface SiStructureModel {

	getContent(): SiContent|null;

	getControls(): SiControl[];

	getMessages(): Message[];
}
