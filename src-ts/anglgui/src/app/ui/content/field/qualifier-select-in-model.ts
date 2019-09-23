
import { MessageFieldModel } from 'src/app/ui/content/field/message-field-model';
import { SiQualifier } from 'src/app/si/model/entity/si-qualifier';

export interface QualifierSelectInModel extends MessageFieldModel {

	getApiUrl(): string;

	getMin(): number;

	getMax(): number|null;

	getValues(): SiQualifier[];

	setValues(values: SiQualifier[]): void;
}
