
import { SiField } from 'src/app/si/model/entity/si-field';
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from '@angular/core';
import { StringOutFieldComponent } from 'src/app/ui/content/field/comp/string-out-field/string-out-field.component';
import { StringFieldModel } from 'src/app/ui/content/field/string-field-model';
import { OutSiFieldAdapter } from 'src/app/si/model/entity/impl/out-si-field-adapter';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { TypeSiContent } from "src/app/si/model/structure/impl/type-si-content";
import { UiContent } from "src/app/si/model/structure/ui-content";

export class StringOutSiField extends OutSiFieldAdapter implements StringFieldModel {
		
	constructor(private value: string|null) {
		super();
	}

	createContent(): UiContent|null {
		return new TypeSiContent(StringOutFieldComponent, (ref, structure) => {
				ref.instance.model = this;
		});
	}

	getValue(): string | null {
		return this.value;
	}
}
