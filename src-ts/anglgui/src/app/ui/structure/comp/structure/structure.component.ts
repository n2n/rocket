import { Component, OnInit, Input, ViewChild, ElementRef, ChangeDetectorRef, OnDestroy, DoCheck } from '@angular/core';
import { StructureContentDirective } from 'src/app/ui/structure/comp/structure/structure-content.directive';
import { UiStructure } from '../../model/ui-structure';
import { UiContent } from '../../model/ui-content';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { Subscription } from 'rxjs';

@Component({
	// tslint:disable-next-line:component-selector
	selector: '[rocketUiStructure]',
	templateUrl: './structure.component.html',
	styleUrls: ['./structure.component.css']
})
export class StructureComponent implements OnInit, OnDestroy, DoCheck {
	@Input()
	labelVisible = true;
	@Input()
	toolbarVisible = true;
	@Input()
	asideVisible = true;
	@Input()
	contentVisible = true;

	private _uiStructure: UiStructure;

	@ViewChild(StructureContentDirective, { static: true })
	structureContentDirective: StructureContentDirective;

	toolbarUiStructures: UiStructure[] = [];
	private toolbarSubscription: Subscription|null = null;

	constructor(private elRef: ElementRef, private cdRef: ChangeDetectorRef) {
	}

	ngOnInit() {
// 		const componentFactory = this.componentFactoryResolver.resolveComponentFactory(CompactExplorerComponent);

// 		const componentRef = this.zoneContentDirective.viewContainerRef.createComponent(componentFactory);

// 		(<ZoneComponent> componentRef.instance).data = {};
	}

	ngOnDestroy() {
		this.clear();
	}

	ngDoCheck() {
		if (!this.uiStructure) {
			return;
		}

		const classList = this.elRef.nativeElement.classList;

		if (this.uiStructure.isItemCollection()) {
			if (!classList.contains('rocket-item-collection')) {
				classList.add('rocket-item-collection');
			}
		} else {
			if (classList.contains('rocket-item-collection')) {
				classList.remove('rocket-item-collection');
			}
		}

		if (this.uiStructure.isDoubleItem()) {
			if (!classList.contains('rocket-double-item')) {
				classList.add('rocket-double-item');
			}
		} else {
			if (classList.contains('rocket-double-item')) {
				classList.remove('rocket-double-item');
			}
		}
	}

	private clear() {
		this._uiStructure = null;

		if (this.toolbarSubscription) {
			this.toolbarSubscription.unsubscribe();
			this.toolbarSubscription = null;
		}
	}

	@Input()
	set uiStructure(uiStructure: UiStructure) {
		this.clear();

		this._uiStructure = uiStructure;
		this.applyCssClass();

		this.toolbarSubscription = uiStructure.getToolbarChildren$().subscribe((toolbarUiStructures) => {
			this.toolbarUiStructures = toolbarUiStructures;
			// if (!uiStructure.disposed) {
			// 	this.cdRef.detectChanges();
			// }
		});

		const classList = this.elRef.nativeElement.classList;
		classList.add('rocket-level-' + uiStructure.level);
	}

	get uiStructure(): UiStructure {
		return this._uiStructure;
	}

	get contentStructuresAvailable(): boolean {
		return this.uiStructure.getContentChildren().length > 0;
	}

	get uiContent(): UiContent|null {
		if (this._uiStructure.model) {
			return this._uiStructure.model.getContent();
		}

		return null;
	}

	get asideUiContents(): UiContent[] {
		if (this._uiStructure.model) {
			return this._uiStructure.model.getAsideContents();
		}

		return [];
	}

	get contentUiStructures(): UiStructure[] {
		return this._uiStructure.getContentChildren();
	}

	getType(): UiStructureType|null {
		return this.uiStructure.type;
	}

	getLabel(): string|null {
		return this.uiStructure.label;
	}

	isItemContext(): boolean {
		if (this.uiStructure.type !== UiStructureType.ITEM) {
			return false;
		}

		return !!this.uiStructure.getChildren().find(child => child.type === UiStructureType.ITEM);
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
