
import { MessageFieldModel } from 'src/app/ui/content/field/message-field-model';
import { SiCrumbGroup } from 'src/app/si/model/entity/impl/meta/si-crumb';

export interface InputInFieldModel extends MessageFieldModel {

	getValue(): string|null;

	setValue(value: string|null): void;

	getType(): string;

	getMaxlength(): number|null;

	getMin(): number|null;

	getMax(): number|null;

	getStep(): number|null;

	getPrefixAddons(): SiCrumbGroup[];

	getSuffixAddons(): SiCrumbGroup[];
}
