
import { MessageFieldModel } from 'src/app/si/content/field/message-field-model';
import { SiEntryQualifier } from 'src/app/si/model/content/si-qualifier';

export interface QualifierSelectInModel extends MessageFieldModel {

	getApiUrl(): string;

	getMin(): number;

	getMax(): number|null;

	getValues(): SiEntryQualifier[];

	setValues(values: SiEntryQualifier[]): void;
}
