import { Directive, NgModuleFactoryLoader } from '@angular/core';
import { ViewContainerRef, Input, ComponentFactoryResolver } from '@angular/core';
import { UiStructure } from '../../model/ui-structure';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { UiContent } from '../../model/ui-content';

@Directive({
	selector: '[rocketUiContent]'
})
export class StructureContentDirective {
	@Input()
	public uiStructure: UiStructure;
	private _uiContent: UiContent|null = null;

	constructor(public viewContainerRef: ViewContainerRef,
			private componentFactoryResolver: ComponentFactoryResolver,
			private moduleLoader: NgModuleFactoryLoader) {
// 		viewContainerRef.element.nativeElement.classList.add('rocket-control');
	}

	@Input() set uiContent(uiContent: UiContent|null) {
		if (this._uiContent === uiContent) {
			return;
		}

		console.log(this.moduleLoader.load('src/app/si/si.module').then(() => { console.log('here') }));

		this._uiContent = uiContent;
		this.viewContainerRef.clear();

		if (!this.uiStructure) {
			throw new IllegalSiStateError('Unknown UiStructure for content directive.');
		}

		if (uiContent) {
				uiContent.initComponent(this.viewContainerRef, this.componentFactoryResolver, this.uiStructure);
		}
	}

	get uiContent(): UiContent|null {
		return this._uiContent;
	}
}
