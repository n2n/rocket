


import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { SiFieldError } from "src/app/si/model/input/si-field-error";
import { SiField } from "src/app/si/model/content/si-field";

export abstract class SiFieldAdapter implements SiField {
	messages: string[] = [];
	
	abstract initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver): ComponentRef<any>;
    
	abstract hasInput(): boolean;
	
	abstract readInput(): object;
	
	getMessages(): string[] {
		return this.messages;	
	}
	
	handleError(error: SiFieldError): void {
		this.messages.push(...error.getAllMessages());
	}
	
	resetError(): void {
		this.messages = [];
	}
}