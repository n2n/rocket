
import { SiField } from "src/app/si/model/content/si-field";
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from "@angular/core";
import { InputInFieldComponent } from "src/app/ui/content/field/comp/input-in-field/input-in-field.component";
import { FileInFieldComponent } from "src/app/ui/content/field/comp/file-in-field/file-in-field.component";
import { FileInFieldModel } from "src/app/ui/content/field/file-in-field-model";
import { InSiFieldAdapter } from "src/app/si/model/content/impl/in-si-field-adapter";
import { SiCommanderService } from "src/app/si/model/si-commander.service";

export class FileInSiField extends InSiFieldAdapter implements FileInFieldModel {
	private uploadedFile: File|null = null;
	public mandatory: boolean = false;
	public mimeTypes: string[] = [];
	public extensions: string[] = [];
	
	constructor(public value: SiFile|null) {
        super();
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
	
    getMimeTypes(): string[] {
        throw new Error("Method not implemented.");
    }
    
    removeFile(): void {
        throw new Error("Method not implemented.");
    }
    
    uploadFile(file: File): void {
        throw new Error("Method not implemented.");
    }
    
    getSiFile(): SiFile|null {
        throw new Error("Method not implemented.");
    }
    
	initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver,
			commanderService: SiCommanderService): ComponentRef<any> {
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
	    });
	    
	    return componentRef;
	}
}

export interface SiFile {
	valid: boolean;
	name: string;
	url: string|null;
	thumbUrl: string|null;
		
}