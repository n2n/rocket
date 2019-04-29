import { Directive } from '@angular/core';
import { ViewContainerRef, Input } from "@angular/core";

@Directive({
  selector: '[rocketStructureContent]'
})
export class StructureContentDirective {

	constructor(public viewContainerRef: ViewContainerRef) {
//		viewContainerRef.element.nativeElement.classList.add('rocket-control');
	}
	
}
