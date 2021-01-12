import { MessageFieldModel } from '../../common/comp/message-field-model';
import { SiCrumbGroup } from './si-crumb';

export interface CrumbFieldModel extends MessageFieldModel {

	getSiCrumbGroups(): SiCrumbGroup[];
}
