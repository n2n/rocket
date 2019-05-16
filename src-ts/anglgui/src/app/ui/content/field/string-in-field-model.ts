import { StringFieldModel } from "src/app/ui/content/field/string-field-model";

export interface StringInFieldModel extends StringFieldModel {
	
	setValue(value: string|null);
	
	getMaxlength(): number|null;
}