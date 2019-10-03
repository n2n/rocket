
import { InSiFieldAdapter } from 'src/app/si/model/entity/impl/in-si-field-adapter';
import { SiQualifier } from 'src/app/si/model/entity/si-qualifier';
import { QualifierSelectInModel } from 'src/app/ui/content/field/qualifier-select-in-model';
// tslint:disable-next-line: max-line-length
import { QualifierSelectInFieldComponent } from 'src/app/ui/content/field/comp/qualifier-select-in-field/qualifier-select-in-field.component';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { TypeSiContent } from 'src/app/si/model/structure/impl/type-si-content';
import { Message } from 'src/app/util/i18n/message';

export class QualifierSelectInSiField extends InSiFieldAdapter implements QualifierSelectInModel {

	public min = 0;
	public max: number|null = null;

	constructor(public apiUrl: string, public values: SiQualifier[] = []) {
		super();
	}

	readInput(): object {
		return { values: this.values };
	}

	getApiUrl(): string {
		return this.apiUrl;
	}

	getValues(): SiQualifier[] {
		return this.values;
	}

	setValues(values: SiQualifier[]) {
		this.values = values;
		this.validate();
	}

	getMin(): number {
		return this.min;
	}

	getMax(): number|null {
		return this.max;
	}

	private validate() {
		this.messages = [];

		if (this.values.length < this.min) {
			this.messages.push(Message.createText('min front err'));
		}

		if (this.max && this.values.length > this.max) {
			this.messages.push(Message.createText('max front err'));
		}
	}

	copy() {
		const copy = new QualifierSelectInSiField(this.apiUrl, this.values);
		copy.min = this.min;
		copy.max = this.max;
		return copy;
	}

	createContent(): SiContent|null {
		return new TypeSiContent(QualifierSelectInFieldComponent, (ref, structure) => {
			ref.instance.model = this;
			ref.instance.siStructure = structure;
		});
	}

// 	initComponent(viewContainerRef: ViewContainerRef,
// 			componentFactoryResolver: ComponentFactoryResolver,
// 			commanderService: SiCommanderService): ComponentRef<any> {
// 		const componentFactory = componentFactoryResolver.resolveComponentFactory(QualifierSelectInFieldComponent);
//
// 		const componentRef = viewContainerRef.createComponent(componentFactory);
//
// 		const component = componentRef.instance;
// 		component.model = this;
//
// 		return componentRef;
// 	}
}
