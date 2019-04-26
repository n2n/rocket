import { Directive, Input, TemplateRef, ViewContainerRef, ComponentFactoryResolver } from '@angular/core';
import { SiField } from "src/app/si/model/content/si-field";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { OnInit } from "@angular/core";

@Directive({
  selector: '[rocketUiField]'
})
export class FieldDirective implements OnInit {

	@Input() siFieldDeclaration: SiFieldDeclaration;
	@Input() siEntry: SiEntry;
	
	private _siField: SiField|null;
	
	constructor(private componentFactoryResolver: ComponentFactoryResolver, 
			private templateRef: TemplateRef<any>, private viewContainerRef: ViewContainerRef) { 
		
	}
	
	ngOnInit() {
		this.viewContainerRef.clear();
		
		let siField: SiField|null;
		if (this.siFieldDeclaration.fieldId
				&& null != (siField = this.siEntry.selectedBuildup.getFieldById(this.siFieldDeclaration.fieldId))) {
			siField.initComponent(this.viewContainerRef, this.componentFactoryResolver);
		}
	}

}
