import { Directive } from '@angular/core';
import { ViewContainerRef, Input, ComponentFactoryResolver } from "@angular/core";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";

@Directive({
  selector: '[rocketStructureContent]'
})
export class StructureContentDirective {

	private content: SiStructureContent|null = null;
	
	constructor(public viewContainerRef: ViewContainerRef, 
			private componentFactoryResolver: ComponentFactoryResolver) {
//		viewContainerRef.element.nativeElement.classList.add('rocket-control');
	}
	
	@Input() set siStructureContent(content: SiStructureContent|null) {
		if (this.content === content) {
			return;
		}
		
		this.content = content;
		this.viewContainerRef.clear();
		
		if (content) {
			content.initComponent(this.viewContainerRef, this.componentFactoryResolver)
		}
	}
	
}
