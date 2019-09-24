
import { SiField } from 'src/app/si/model/entity/si-field';
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from '@angular/core';
import { InputInFieldComponent } from 'src/app/ui/content/field/comp/input-in-field/input-in-field.component';
import { InSiFieldAdapter } from 'src/app/si/model/entity/impl/in-si-field-adapter';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { SiContent } from '../../../structure/si-content';
import { TypeSiContent } from "src/app/si/model/structure/impl/type-si-content";
import { InputInFieldModel } from "src/app/ui/content/field/input-in-field-model";

export class StringInSiField extends InSiFieldAdapter implements InputInFieldModel {
	public mandatory = false;
	public minlength: number|null = null;
	public maxlength: number|null = null;

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

	private validate() {
		this.messages = [];

		if (this.mandatory && this.value === null) {
			this.messages.push('mandatory front err');
		}

		if (this.minlength && this.value && this.value.length < this.minlength) {
			this.messages.push('minlength front err');
		}

		if (this.maxlength && this.value && this.value.length > this.maxlength) {
			this.messages.push('maxlength front err');
		}
	}

	copy(): SiField {
		const copy = new StringInSiField(this.value, this.multiline);
		copy.mandatory = this.mandatory;
		copy.minlength = this.minlength;
		copy.maxlength = this.maxlength;
		return copy;
	}

	getContent(): SiContent|null {
        return new TypeSiContent(InputInFieldComponent, (ref, structure) => {
            ref.instance.model = this;
        });
    }

//	initComponent(viewContainerRef: ViewContainerRef,
//			componentFactoryResolver: ComponentFactoryResolver,
//			commanderService: SiCommanderService): ComponentRef<any> {
//		const componentFactory = componentFactoryResolver.resolveComponentFactory(InputInFieldComponent);
//
//		const componentRef = viewContainerRef.createComponent(componentFactory);
//
//		const component = componentRef.instance;
//		component.model = this;
//
//		return componentRef;
//	}
}
