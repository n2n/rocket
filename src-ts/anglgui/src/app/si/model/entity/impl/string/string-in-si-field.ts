
import { SiField } from 'src/app/si/model/entity/si-field';
import { InputInFieldComponent } from 'src/app/ui/content/field/comp/input-in-field/input-in-field.component';
import { InSiFieldAdapter } from 'src/app/si/model/entity/impl/in-si-field-adapter';
import { SiContent } from '../../../structure/si-content';
import { TypeSiContent } from 'src/app/si/model/structure/impl/type-si-content';
import { InputInFieldModel } from 'src/app/ui/content/field/input-in-field-model';
import { SiCrumbGroup } from '../meta/si-crumb';
import { Message } from 'src/app/util/i18n/message';

export class StringInSiField extends InSiFieldAdapter implements InputInFieldModel {
	public mandatory = false;
	public minlength: number|null = null;
	public maxlength: number|null = null;
	public prefixAddons: SiCrumbGroup[] = [];
	public suffixAddons: SiCrumbGroup[] = [];

	constructor(public value: string|null, public multiline: boolean = false) {
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
			this.messages.push(Message.createText('mandatory front err'));
		}

		if (this.minlength && this.value && this.value.length < this.minlength) {
			this.messages.push(Message.createText('minlength front err'));
		}

		if (this.maxlength && this.value && this.value.length > this.maxlength) {
			this.messages.push(Message.createText('maxlength front err'));
		}
	}

	copy(): SiField {
		const copy = new StringInSiField(this.value, this.multiline);
		copy.mandatory = this.mandatory;
		copy.minlength = this.minlength;
		copy.maxlength = this.maxlength;
		return copy;
	}

	createContent(): SiContent|null {
		return new TypeSiContent(InputInFieldComponent, (ref) => {
			ref.instance.model = this;
		});
	}

// 	initComponent(viewContainerRef: ViewContainerRef,
// 			componentFactoryResolver: ComponentFactoryResolver,
// 			commanderService: SiCommanderService): ComponentRef<any> {
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
