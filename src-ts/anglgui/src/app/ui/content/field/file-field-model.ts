import { SiFile } from 'src/app/si/model/entity/impl/file/file-in-si-field';
import { MessageFieldModel } from 'src/app/ui/content/field/message-field-model';
import { SiZone } from 'src/app/si/model/structure/si-zone';

export interface FileFieldModel extends MessageFieldModel {

	getSiZone(): SiZone;

	getSiFile(): SiFile|null;
}
