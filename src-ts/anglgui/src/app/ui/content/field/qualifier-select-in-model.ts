
import { MessageFieldModel } from "src/app/ui/content/field/message-field-model";
import { SiQualifier } from "src/app/si/model/entity/si-qualifier";
import { SiZone } from "src/app/si/model/structure/si-zone";

export interface QualifierSelectInModel extends MessageFieldModel {
	
	getSiZone(): SiZone;
	
	getApiUrl(): string;
	
	getMin(): number;
	
	getMax(): number|null;
	
	getValues(): SiQualifier[];
	
	setValues(values: SiQualifier[]);
}