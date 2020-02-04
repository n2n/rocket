import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { InputInFieldModel } from '../comp/input-in-field-model';
import { SiCrumbGroup } from '../../meta/model/si-crumb';
import { SiField } from '../../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { InputInFieldComponent } from '../comp/input-in-field/input-in-field.component';
import { Message } from 'src/app/util/i18n/message';
import { SiGenericValue } from '../../../si-generic-value';


export class StringInSiField extends InSiFieldAdapter implements InputInFieldModel {
	public mandatory = false;
	public minlength: number|null = null;
	public maxlength: number|null = null;
	public prefixAddons: SiCrumbGroup[] = [];
	public suffixAddons: SiCrumbGroup[] = [];

	constructor(public label: string, public value: string|null, public multiline: boolean = false) {
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
		return { value: this.value };
	}

	getValue(): string|null {
		return this.value;
	}

	getMaxlength(): number|null {
		return this.maxlength;
	}

	setValue(value: string|null) {
		this.value = value;
		this.validate();
	}

	getPrefixAddons(): SiCrumbGroup[] {
		return this.prefixAddons;
	}

	getSuffixAddons(): SiCrumbGroup[] {
		return this.suffixAddons;
	}

	private validate() {
		this.messages = [];

		if (this.mandatory && this.value === null) {
			this.messages.push(Message.createCode('mandatory_err', new Map([[ '{field}', this.label ]])));
		}

		if (this.minlength && this.value && this.value.length < this.minlength) {
			this.messages.push(Message.createCode('minlength_err', new Map([[ '{field}', this.label ]])));
		}

		if (this.maxlength && this.value && this.value.length > this.maxlength) {
			this.messages.push(Message.createCode('maxlength_err', new Map([[ '{field}', this.label ]])));
		}
	}

	copy(): SiField {
		const copy = new StringInSiField(this.label, this.value, this.multiline);
		copy.mandatory = this.mandatory;
		copy.minlength = this.minlength;
		copy.maxlength = this.maxlength;
		return copy;
	}

	isGeneric() {
		return true;
	}

	readGenericValue(): SiGenericValue {
		return new SiGenericValue(this.value === null ? null : new String(this.value));
	}

	writeGenericValue(genericValue: SiGenericValue): boolean {
		if (genericValue.isNull()) {
			this.value = null;
			return true;
		}

		if (genericValue.isInstanceOf(String)) {
			this.value = genericValue.readInstance(String).toString();
			return true;
		}

		return false;
	}

	createUiContent(): UiContent {
		return new TypeUiContent(InputInFieldComponent, (ref) => {
			ref.instance.model = this;
		});
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
