import { Directive } from '@angular/core';
import { ViewContainerRef, Input, ComponentFactoryResolver } from '@angular/core';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';

@Directive({
  selector: '[rocketContent]'
})
export class StructureContentDirective {

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

		if (siContent) {
			siContent.initComponent(this.viewContainerRef, this.componentFactoryResolver, this.siCommanderService);
		}
	}

	get siContent(): SiContent|null {
		return this._siContent;
	}
}
