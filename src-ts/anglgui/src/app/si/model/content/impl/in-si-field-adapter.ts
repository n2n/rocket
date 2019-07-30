
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { SiFieldError } from "src/app/si/model/input/si-field-error";
import { SiFieldAdapter } from "src/app/si/model/content/impl/si-field-adapter";
import { SiCommanderService } from "src/app/si/model/si-commander.service";
import { SiContent } from "src/app/si/model/structure/si-content";

export abstract class InSiFieldAdapter extends SiFieldAdapter {
    
	hasInput(): boolean {
		return true;
	}
	
	abstract readInput(): object;
	
	abstract copy(): SiField;
	
	abstract getContent(): SiContent|null;
}