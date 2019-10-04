import { SiFile } from 'src/app/si/model/entity/impl/file/file-in-si-field';
import { MessageFieldModel } from 'src/app/ui/content/field/message-field-model';
import { UiZone } from 'src/app/si/model/structure/ui-zone';

export interface FileFieldModel extends MessageFieldModel {

	getSiFile(): SiFile|null;
}
