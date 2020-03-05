
import { Message } from 'src/app/util/i18n/message';
import { SiEntryQualifier } from '../../../si-qualifier';
import { QualifierSelectInModel } from '../comp/qualifier-select-in-model';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { QualifierSelectInFieldComponent } from '../comp/qualifier-select-in-field/qualifier-select-in-field.component';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

export class QualifierSelectInSiField extends InSiFieldAdapter implements QualifierSelectInModel {

	public min = 0;
	public max: number|null = null;

	constructor(public apiUrl: string, public label: string, public values: SiEntryQualifier[] = []) {
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
			if (this.max === 1 || this.min === 1) {
				this.messages.push(Message.createCode('mandatory_err', new Map([['{field}', this.label]])));
			} else {
				this.messages.push(Message.createCode('min_elements_err',
						new Map([['{min}', this.min.toString()], ['{field}', this.label]])));
			}
		}

		if (this.max && this.values.length > this.max) {
			this.messages.push(Message.createCode('max_elements_err',
						new Map([['{max}', this.max.toString()], ['{field}', this.label]])));
		}
	}

	copy() {
		const copy = new QualifierSelectInSiField(this.apiUrl, this.label, this.values);
		copy.min = this.min;
		copy.max = this.max;
		return copy;
	}

	createUiContent(uiStructure: UiStructure): UiContent {
		return new TypeUiContent(QualifierSelectInFieldComponent, (ref) => {
			ref.instance.model = this;
			ref.instance.uiStructure = uiStructure;
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
