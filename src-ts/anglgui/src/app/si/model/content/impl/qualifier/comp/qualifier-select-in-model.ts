import { SiEntryQualifier } from 'src/app/si/model/content/si-qualifier';
import { MessageFieldModel } from '../../common/comp/message-field-model';

export interface QualifierSelectInModel extends MessageFieldModel {

	getApiUrl(): string;

	getMin(): number;

	getMax(): number|null;

	getPickables(): SiEntryQualifier[]|null;

	getValues(): SiEntryQualifier[];

	setValues(values: SiEntryQualifier[]): void;
}
