
import { InSiFieldAdapter } from 'src/app/si/model/entity/impl/in-si-field-adapter';
import { SiEntryQualifier } from 'src/app/si/model/entity/si-qualifier';
import { QualifierSelectInModel } from 'src/app/ui/content/field/qualifier-select-in-model';
import { QualifierSelectInFieldComponent } from 'src/app/ui/content/field/comp/qualifier-select-in-field/qualifier-select-in-field.component';
import { UiZone } from 'src/app/si/model/structure/ui-zone';
import { UiContent } from 'src/app/si/model/structure/ui-content';
import { TypeSiContent } from 'src/app/si/model/structure/impl/type-si-content';
import { Message } from 'src/app/util/i18n/message';

export class QualifierSelectInSiField extends InSiFieldAdapter implements QualifierSelectInModel {

	public min = 0;
	public max: number|null = null;

	constructor(public apiUrl: string, public values: SiEntryQualifier[] = []) {
		super();
	}

	readInput(): object {
		return { values: this.values };
	}

	getApiUrl(): string {
		return this.apiUrl;
	}

	getValues(): SiEntryQualifier[] {
		return this.values;
	}

	setValues(values: SiEntryQualifier[]) {
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

	createContent(): UiContent|null {
		return new TypeSiContent(QualifierSelectInFieldComponent, (ref, structure) => {
			ref.instance.model = this;
			ref.instance.siStructure = structure;
		});
	}

// 	initComponent(viewContainerRef: ViewContainerRef,
// 			componentFactoryResolver: ComponentFactoryResolver,
// 			commanderService: SiUiService): ComponentRef<any> {
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
