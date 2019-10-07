import { SiFile } from 'src/app/si/model/content/impl/file/file-in-si-field';
import { MessageFieldModel } from 'src/app/si/content/field/message-field-model';
import { UiZone } from 'src/app/si/model/structure/ui-zone';

export interface FileFieldModel extends MessageFieldModel {

	getSiFile(): SiFile|null;
}
