import { Component, OnInit, Input, ViewChild, ElementRef } from '@angular/core';
import { StructureContentDirective } from 'src/app/ui/structure/comp/structure/structure-content.directive';
import { UiStructure } from '../../model/ui-structure';
import { UiContent } from '../../model/ui-content';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';

@Component({
	// tslint:disable-next-line:component-selector
	selector: '[rocketUiStructure]',
	templateUrl: './structure.component.html',
	styleUrls: ['./structure.component.css']
})
export class StructureComponent implements OnInit {

	@Input()
	toolbarVisible = true;

	private _uiStructure: UiStructure;

	@ViewChild(StructureContentDirective, { static: true })
	structureContentDirective: StructureContentDirective;

	readonly controls: UiContent[] = [];

	constructor(private elRef: ElementRef) {
	}

	ngOnInit() {
// 		const componentFactory = this.componentFactoryResolver.resolveComponentFactory(ListZoneContentComponent);

// 		const componentRef = this.zoneContentDirective.viewContainerRef.createComponent(componentFactory);

// 		(<ZoneComponent> componentRef.instance).data = {};
	}

	@Input()
	set uiStructure(uiStructure: UiStructure) {
		this._uiStructure = uiStructure;
		this.applyCssClass();
	}

	get uiStructure(): UiStructure {
		return this._uiStructure;
	}

	get uiContent(): UiContent|null {
		return this._uiStructure.model.getContent();
	}

	get toolbarUiContents(): UiContent[] {
		const controls: UiContent[] = [];

		controls.push(...this._uiStructure.toolbackUiContents);
		controls.push(...this._uiStructure.model.getToolbarContents());

		return controls;
	}

	getType(): UiStructureType|null {
		return this.uiStructure.type;
	}

	getLabel(): string|null {
		return this.uiStructure.label;
	}

	isMain() {
		return this.getType() === UiStructureType.MAIN_GROUP;
	}

// 	ngDoCheck() {
// 		if (this.currentUiStructure &&
// 				(this.currentUiStructure !== this.uiStructure)) {
// 			this.structureContentDirective.viewContainerRef.clear();
// 			this.currentUiStructure = null;
// 		}
//
// 		if (this.currentUiStructure || !this.uiStructure) {
// 			return;
// 		}
//
// 		this.currentUiStructure = this.uiStructure;
// 		this.currentUiStructure.initComponent(this.structureContentDirective.viewContainerRef,
// 				this.componentFactoryResolver);
//// 		this.structureContentDirective.viewContainerRef.element.nativeElement.childNodes[0].classList.add('rocket-control');
//
// 		this.applyCssClass();
// 	}

	private applyCssClass() {
		const classList = this.elRef.nativeElement.classList;

		classList.remove('rocket-item');
		classList.remove('rocket-group');
		classList.remove('rocket-simple-group');
		classList.remove('rocket-main-group');
		classList.remove('rocket-light-group');
		classList.remove('rocket-panel');

		switch (this.getType()) {
			case UiStructureType.ITEM:
				classList.add('rocket-item');
				break;
			case UiStructureType.SIMPLE_GROUP:
			case UiStructureType.AUTONOMIC_GROUP:
				classList.add('rocket-group');
				classList.add('rocket-simple-group');
				break;
			case UiStructureType.MAIN_GROUP:
				classList.add('rocket-group');
				classList.add('rocket-main-group');
				break;
			case UiStructureType.LIGHT_GROUP:
				classList.add('rocket-group');
				classList.add('rocket-light-group');
				break;
			case UiStructureType.PANEL:
				classList.add('rocket-panel');
				break;
		}
	}

	get loaded(): boolean {
		return !!this.uiStructure.model;
	}

}
