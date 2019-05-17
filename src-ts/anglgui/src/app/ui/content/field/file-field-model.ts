import { SiFile } from "src/app/si/model/content/impl/file-in-si-field";
import { MessageFieldModel } from "src/app/ui/content/field/message-field-model";

export interface FileFieldModel extends MessageFieldModel {
	
	getSiFile(): SiFile|null;
}