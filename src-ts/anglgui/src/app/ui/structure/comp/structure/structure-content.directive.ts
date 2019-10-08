import { Directive } from '@angular/core';
import { ViewContainerRef, Input, ComponentFactoryResolver } from '@angular/core';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { UiStructure } from "src/app/si/model/structure/si-structure";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

@Directive({
	selector: '[rocketUiContent]'
})
export class StructureContentDirective {
		@Input()
		public uiStructure: UiStructure;
	private _siContent: SiContent|null = null;

	constructor(public viewContainerRef: ViewContainerRef,
			private componentFactoryResolver: ComponentFactoryResolver,
			private siCommanderService: SiCommanderService) {
// 		viewContainerRef.element.nativeElement.classList.add('rocket-control');
	}

	@Input() set siContent(siContent: SiContent|null) {
		if (this._siContent === siContent) {
			return;
		}
		
		this._siContent = siContent;
		this.viewContainerRef.clear();

		if (!this.uiStructure) {
						throw new IllegalSiStateError('Unknown UiStructure for content directive.');
				}
		
		if (siContent) {
				siContent.initComponent(this.viewContainerRef, this.componentFactoryResolver, this.uiStructure);
		}
	}

	get siContent(): SiContent|null {
		return this._siContent;
	}
}
