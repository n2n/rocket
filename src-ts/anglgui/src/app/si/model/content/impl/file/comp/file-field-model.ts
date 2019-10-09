import { MessageFieldModel } from '../../common/comp/message-field-model';
import { SiFile } from '../model/file-in-si-field';

export interface FileFieldModel extends MessageFieldModel {

	getSiFile(): SiFile|null;
}
