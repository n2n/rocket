import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { Message } from 'src/app/util/i18n/message';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { CkeInFieldComponent } from '../comp/cke-in-field/cke-in-field.component';
import { CkeInModel } from '../comp/cke-in-model';

export enum CkeMode {
	SIMPLE = 'simple',
	NORMAL = 'normal',
	ADVANCED = 'advanced'
}

export class CkeInSiField extends InSiFieldAdapter implements CkeInModel {

	public mandatory = false;
	public minlength: number|null = null;
	public maxlength: number|null = null;
	public mode = CkeMode.SIMPLE;

	constructor(public label: string, public value: string|null) {
		super();
		this.validate();
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

	getMode(): CkeMode {
		return this.mode;
	}

	private validate() {
		this.messages = [];

		if (this.mandatory && this.value === null) {
			this.messages.push(Message.createCode('mandatory_err', new Map([['{field}', this.label]])));
		}

		if (this.minlength && this.value && this.value.length < this.minlength) {
			this.messages.push(Message.createCode('minlength_err', new Map([['{field}', this.label], ['{minlength}', this.minlength.toString()]])));
		}

		if (this.maxlength && this.value && this.value.length > this.maxlength) {
			this.messages.push(Message.createCode('maxlength_err', new Map([['{field}', this.label], ['{maxlength}', this.maxlength.toString()]])));
		}
	}

	// copy(): SiField {
	// 	const copy = new StringInSiField(this.label, this.value, this.multiline);
	// 	copy.mandatory = this.mandatory;
	// 	copy.minlength = this.minlength;
	// 	copy.maxlength = this.maxlength;
	// 	return copy;
	// }

	isGeneric() {
		return true;
	}

	copyValue(): SiGenericValue {
		return new SiGenericValue(this.value === null ? null : new String(this.value));
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		if (genericValue.isNull()) {
			this.value = null;
			return Promise.resolve();
		}

		if (genericValue.isInstanceOf(String)) {
			this.value = genericValue.readInstance(String).valueOf();
			return Promise.resolve();
		}

		throw new GenericMissmatchError('String expected.');
	}

	createUiContent(): UiContent {
		return new TypeUiContent(CkeInFieldComponent, (ref) => {
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
