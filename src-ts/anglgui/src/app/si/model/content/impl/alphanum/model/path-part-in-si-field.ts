import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { AutoConfig, InputInFieldModel } from '../comp/input-in-field-model';
import { SiCrumbGroup } from '../../meta/model/si-crumb';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { InputInFieldComponent } from '../comp/input-in-field/input-in-field.component';
import { Message } from 'src/app/util/i18n/message';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { TextAreaInFieldModel } from '../comp/textarea-in-field-model';
import { SiInputResetPoint } from '../../../si-input-reset-point';
import { CallbackInputResetPoint as CallbackSiInputResetPoint } from '../../common/model/callback-si-input-reset-point';
import { SiField } from '../../../si-field';


export class PathPartInSiField extends InSiFieldAdapter implements InputInFieldModel, TextAreaInFieldModel {
	public mandatory = false;
	public minlength: number|null = null;
	public maxlength: number|null = null;
	public prefixAddons: SiCrumbGroup[] = [];
	public suffixAddons: SiCrumbGroup[] = [];
	private pBasedOnField: SiField|null = null;
	private tmpValue: string|null = null;
	private auto = true;

	constructor(public label: string, public value: string|null) {
		super();
		this.validate();
	}

	getType(): string {
		return 'text';
	}

	getMin(): number|null {
		return null;
	}

	getMax(): number|null {
		return null;
	}

	getStep(): number|null {
		return null;
	}

	readInput(): object {
		return { value: this.getValue() };
	}

	createInputResetPoint(): Promise<SiInputResetPoint> {
		return Promise.resolve(new CallbackSiInputResetPoint(this.value, (value) => { this.value = value; }));
	}

	private cleanPathPart(input: string|null): string|null {
		return input?.trim().toLocaleLowerCase().replaceAll(/[^\p{L}\p{Nd}_]+/gu, '-') ?? null;
	}

	getValue(): string|null {
		if (this.auto && this.basedOnField && this.basedOnField.readValue) {
			const genericValue = this.basedOnField.readValue();
			if (genericValue.isNull()) {
				return null;
			}

			if (genericValue.isStringRepresentable()) {
				return this.cleanPathPart(genericValue.readString());
			}
		}

		if (null !== this.tmpValue) {
			return this.tmpValue;
		}

		return this.value;
	}

	getMaxlength(): number|null {
		return this.maxlength;
	}

	setValue(value: string|null): void {
		if (null === value) {
			this.tmpValue = '';
			return;
		}

		this.tmpValue = value;
	}

	getPrefixAddons(): SiCrumbGroup[] {
		return this.prefixAddons;
	}

	getSuffixAddons(): SiCrumbGroup[] {
		return this.suffixAddons;
	}

	private validate(): void {
		this.messagesCollection.clear();

		if (this.mandatory && this.value === null) {
			this.messagesCollection.push(Message.createCode('mandatory_err', new Map([['{field}', this.label]])));
		}

		if (this.minlength && this.value && this.value.length < this.minlength) {
			this.messagesCollection.push(Message.createCode('minlength_err',
					new Map([['{field}', this.label], ['{minlength}', this.minlength.toString()]])));
		}

		if (this.maxlength && this.value && this.value.length > this.maxlength) {
			this.messagesCollection.push(Message.createCode('maxlength_err',
					new Map([['{field}', this.label], ['{maxlength}', this.maxlength.toString()]])));
		}
	}

	set basedOnField(basedOnField: SiField|null) {
		this.pBasedOnField = basedOnField;
	}

	get basedOnField(): SiField|null {
		return this.pBasedOnField;
	}

	copyValue(): Promise<SiGenericValue> {
		return Promise.resolve(new SiGenericValue(this.value));
	}

	pasteValue(genericValue: SiGenericValue): Promise<boolean> {
		if (!genericValue.isNull() && !genericValue.isStringRepresentable()) {
			return Promise.resolve(false);
		}

		this.value = genericValue.readStringOrNull();
		return Promise.resolve(true);
	}

	createUiContent(): UiContent {
		return new TypeUiContent(InputInFieldComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	onBlur(): void {
		if (null !== this.tmpValue) {
			if (this.tmpValue.length === 0) {
				this.value = null;
			} else {
				this.value = this.cleanPathPart(this.tmpValue);
			}
		}
		this.tmpValue = null;
		this.validate();
	}

	onFocus(): void {
		this.messagesCollection.clear();
	}

	getAutoConfig(): AutoConfig|null {
		if (!this.basedOnField?.readValue) {
			return null;
		}

		return {
			enabledTextCode: 'generated_txt',
			disabledTextCode: 'manual_txt'
		};
	}

	isAuto(): boolean {
		return this.auto;
	}

	setAuto(auto: boolean): void {
		this.value = this.getValue();
		this.auto = auto;
	}

// 	initComponent(viewContainerRef: ViewContainerRef,
// 			componentFactoryResolver: ComponentFactoryResolver,
// 			commanderService: SiUiService): ComponentRef<any> {
// 		const componentFactory = componentFactoryResolver.resolveComponentFactory(InputInFieldComponent);
//
// 		const componentRef = viewContainerRef.createComponent(componentFactory);
//
// 		const component = componentRef.instance;
// 		component.model = this;
//
// 		return componentRef;
// 	}
}
