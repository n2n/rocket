import { Directive } from '@angular/core';
import { ViewContainerRef, Input } from "@angular/core";

@Directive({
  selector: '[rocketZoneContent]'
})
export class ZoneContentDirective {

	
	constructor(public viewContainerRef: ViewContainerRef) { 
	}
	
	

}
