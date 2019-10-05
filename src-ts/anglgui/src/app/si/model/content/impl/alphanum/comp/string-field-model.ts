
import { MessageFieldModel } from "src/app/ui/content/field/message-field-model";

export interface StringFieldModel extends MessageFieldModel {
	
	getValue(): string|null;
}