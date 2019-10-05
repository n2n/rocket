
import { SiField } from "src/app/si/model/entity/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { IllegalSiStateError } from "src/app/si/util/illegal-si-state-error";
import { SiFieldError } from "src/app/si/model/input/si-field-error";
import { SiFieldAdapter } from "src/app/si/model/entity/impl/si-field-adapter";
import { SiUiService } from "src/app/si/manage/si-ui.service";

export abstract class OutSiFieldAdapter extends SiFieldAdapter {
	
	hasInput(): boolean {
		return false;
	}
	
	readInput(): Map<string, string | number | boolean | File | null> {
				throw new IllegalSiStateError('no input');
		}
	
	copy(): SiField {
		return this;
	}
}