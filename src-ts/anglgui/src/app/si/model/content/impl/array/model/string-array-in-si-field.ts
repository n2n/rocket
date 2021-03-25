import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { Message } from 'src/app/util/i18n/message';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { StringArrayInModel } from '../comp/string-array-in-model';
import { StringArrayInComponent } from '../comp/string-array-in/string-array-in.component';


export class StringArrayInSiField extends InSiFieldAdapter implements StringArrayInModel {
	public min = 0;
	public max: number|null = null;

	constructor(public label: string, public values = []) {
		super();
		this.validate();
	}


	getMin(): number {
		return this.min;
	}

	getMax(): number|null {
		return this.max;
	}

	readInput(): object {
		return { values: this.values };
	}

	getValues(): string[] {
		return this.values;
	}

	setValues(values: string[]): void {
		this.values = values;
		this.validate();
	}

	private validate(): void {
		this.resetError();

		if (this.min && this.values.length < this.min) {
			this.addMessage(Message.createCode('min_err', new Map([['{field}', this.label], ['{min}', this.min.toString()]])));
		}

		if (this.max && this.values.length > this.max) {
			this.addMessage(Message.createCode('max_err', new Map([['{field}', this.label], ['{max}', this.max.toString()]])));
		}
	}

	// copy(): SiField {
	// 	const copy = new StringInSiField(this.label, this.value, this.multiline);
	// 	copy.mandatory = this.mandatory;
	// 	copy.minlength = this.minlength;
	// 	copy.maxlength = this.maxlength;
	// 	return copy;
	// }

	isGeneric(): boolean {
		return true;
	}

	copyValue(): Promise<SiGenericValue> {
		return new SiGenericValue(this.values === null ? null : new Array(this.values));
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		if (genericValue.isNull()) {
			this.values = [];
			return Promise.resolve();
		}

		// if (genericValue.isInstanceOf(Array<string>)) {
		// 	this.values = genericValue.readInstance(Array<string>).valueOf();
		// 	return Promise.resolve();
		// }

		throw new GenericMissmatchError('Array expected.');
	}

	createUiContent(): UiContent {
		return new TypeUiContent(StringArrayInComponent, (ref) => {
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
