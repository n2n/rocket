


import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { SiFieldError } from "src/app/si/model/input/si-field-error";
import { SiField } from "src/app/si/model/content/si-field";
import { SiZoneError } from "src/app/si/model/structure/si-zone-error";
import { SiCommanderService } from "src/app/si/model/si-commander.service";

export abstract class SiFieldAdapter implements SiField {
	protected messages: string[] = [];
	
	abstract initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver,
			commanderService: SiCommanderService): ComponentRef<any>;
    
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
	
	getZoneErrors(): SiZoneError[] {
		if (this.messages.length > 0) {
			return [new SiFieldZoneError(this, this.messages)];
		}
	
		return [];
	}
}

export class SiFieldZoneError implements SiZoneError {
	constructor(private siField: SiField, private messages: string[]) {
	}
	
    getTitle(): string {
    	return 'title';
    }
    getMessages(): string[] {
        return this.messages;
    }
    
    setHighlighted(highlighted: any): void {
    	throw new Error("Method not implemented.");
    }
    focus(): void {
        throw new Error("Method not implemented.");
    }
	
}