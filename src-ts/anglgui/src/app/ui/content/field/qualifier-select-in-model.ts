
import { MessageFieldModel } from "src/app/ui/content/field/message-field-model";
import { SiQualifier } from "src/app/si/model/content/si-qualifier";
import { SiZone } from "src/app/si/model/structure/si-zone";

export interface QualifierSelectInModel extends MessageFieldModel {
	
	getSiZone(): SiZone;
	
	getApiUrl(): string|null;
	
	getValues(): SiQualifier[];
	
	setValues(values: SiQualifier[]);
}