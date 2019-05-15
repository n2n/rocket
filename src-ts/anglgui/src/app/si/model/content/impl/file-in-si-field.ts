
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { StringOutFieldComponent } from "src/app/ui/content/field/comp/string-out-field/string-out-field.component";
import { InputInFieldComponent } from "src/app/ui/content/field/comp/input-in-field/input-in-field.component";
import { FileInFieldComponent } from "src/app/ui/content/field/comp/file-in-field/file-in-field.component";

export class FileInSiField implements SiField {
	private uploadedFile: File|null = null;
	public mandatory: boolean = false;
	public mimeTypes: string[] = [];
	public extensions: string[] = [];
	
	constructor(public value: SiFile|null) {
		
	}
		
	hasInput(): boolean {
		return true;
	}
	
    readInput(): object {
        return {
            'keep': !!this.value,
            'file': this.uploadedFile
        };
    }
	
	initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(FileInFieldComponent);
	    
	    const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    const component = componentRef.instance;
	    component.mandatory = this.mandatory;
	    component.currentSiFile = this.value;
	    component.mimeTypes = this.mimeTypes;
	    
	    component.currentSiFile$.subscribe(value => {
	    	this.value = value;
	    });
	    component.uploadedFile$.subscribe(file => {
	    	this.uploadedFile = file;
	    })
	    
	    return componentRef;
	}
}

export interface SiFile {
	valid: boolean;
	name: string;
	url: string|null;
	thumbUrl: string|null;
		
}