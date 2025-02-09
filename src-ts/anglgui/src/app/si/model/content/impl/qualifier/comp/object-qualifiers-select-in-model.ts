import { SiObjectQualifier } from 'src/app/si/model/content/si-object-qualifier';
import { MessageFieldModel } from '../../common/comp/message-field-model';
import { SiFrame } from 'src/app/si/model/meta/si-frame';

export interface ObjectQualifiersSelectInModel extends MessageFieldModel {

	getSiFrame(): SiFrame;

	getSiMaskId(): string;

	getMin(): number;

	getMax(): number|null;

	getPickables(): SiObjectQualifier[]|null;

	getValues(): SiObjectQualifier[];

	setValues(values: SiObjectQualifier[]): void;
}
