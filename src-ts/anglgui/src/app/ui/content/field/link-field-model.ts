
import { MessageFieldModel } from "src/app/ui/content/field/message-field-model";

export interface LinkOutModel extends MessageFieldModel {
	isHref(): boolean;
	
	getRef(): string;
	
	getLabel(): string;
}