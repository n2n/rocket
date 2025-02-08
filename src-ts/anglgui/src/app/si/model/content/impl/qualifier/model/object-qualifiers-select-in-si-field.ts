
import { Message } from 'src/app/util/i18n/message';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { ObjectQualifiersSelectInFieldComponent } from '../comp/object-qualifiers-select-in-field/object-qualifiers-select-in-field.component';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { SiInputResetPoint } from '../../../si-input-reset-point';
import { CallbackInputResetPoint } from '../../common/model/callback-si-input-reset-point';
import { ObjectQualifiersSelectInModel } from '../comp/object-qualifiers-select-in-model';
import { SiObjectQualifier } from '../../../si-object-qualifier';

class SiObjectQualifierCollection {
	constructor(public siObjectQualifiers: SiObjectQualifier[]) {
	}
}

export class ObjectQualifiersSelectInSiField extends InSiFieldAdapter implements ObjectQualifiersSelectInModel {
	public min = 0;
	public max: number|null = null;
	public pickables: SiObjectQualifier[]|null = null;

	constructor(public frame: SiFrame, public label: string, public values: SiObjectQualifier[] = []) {
		super();
	}

	readInput(): object {
		return { values: this.values };
	}

	getSiFrame(): SiFrame {
		return this.frame;
	}

	getValues(): SiObjectQualifier[] {
		return this.values;
	}

	setValues(values: SiObjectQualifier[]): void {
		this.values = values;
		this.validate();
	}

	getMin(): number {
		return this.min;
	}

	getMax(): number|null {
		return this.max;
	}

	getPickables(): SiObjectQualifier[]|null {
		return this.pickables;
	}

	private validate(): void {
		this.messagesCollection.clear();

		if (this.values.length < this.min) {
			if (this.max === 1 || this.min === 1) {
				this.messagesCollection.push(Message.createCode('mandatory_err', new Map([['{field}', this.label]])));
			} else {
				this.messagesCollection.push(Message.createCode('min_elements_err',
						new Map([['{min}', this.min.toString()], ['{field}', this.label]])));
			}
		}

		if (this.max !== null && this.values.length > this.max) {
			this.messagesCollection.push(Message.createCode('max_elements_err',
						new Map([['{max}', this.max.toString()], ['{field}', this.label]])));
		}
	}

	createUiContent(uiStructure: UiStructure): UiContent {
		return new TypeUiContent(ObjectQualifiersSelectInFieldComponent, (ref) => {
			ref.instance.model = this;
			ref.instance.uiStructure = uiStructure;
		});
	}

	copyValue(): Promise<SiGenericValue> {
		return Promise.resolve(new SiGenericValue(new SiObjectQualifierCollection(this.values)));
	}

	pasteValue(genericValue: SiGenericValue): Promise<boolean> {
		const siObjectQualifiers = genericValue.readInstance(SiObjectQualifierCollection).siObjectQualifiers;

		const values = [];
		for (const siObjectQualifier of siObjectQualifiers) {
			if (this.max !== null && this.values.length >= this.max) {
				break;
			}

			if (this.pickables === null) {
				// TODO: apply validation constraints
				values.push(siObjectQualifier);
				continue;
			}

			const pickable = this.pickables!.find(
					(p) => p.matchesObjectIdentifier(siObjectQualifier))
			if (pickable) {
				values.push(siObjectQualifier);
				continue;
			}

			return Promise.resolve(false);
		}

		this.values = values;
		return Promise.resolve(true);
	}

	async createInputResetPoint(): Promise<SiInputResetPoint> {
		return new CallbackInputResetPoint([...this.values], (values) => {
			this.values = [...values];
		});
	}

}
