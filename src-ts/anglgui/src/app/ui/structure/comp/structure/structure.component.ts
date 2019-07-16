import { Component, OnInit, Input, ComponentFactoryResolver, ViewChild, ElementRef } from '@angular/core';
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { StructureContentDirective } from "src/app/ui/structure/comp/structure/structure-content.directive";
import { SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiStructure } from "src/app/si/model/structure/si-structure";

@Component({
  selector: 'rocket-ui-structure',
  templateUrl: './structure.component.html',
  styleUrls: ['./structure.component.css']
})
export class StructureComponent implements OnInit {

	private _siStructure: SiStructure;
	
	@ViewChild(StructureContentDirective, { static: true }) structureContentDirective: StructureContentDirective;

    constructor(private elRef: ElementRef, private componentFactoryResolver: ComponentFactoryResolver) { 
    }

	ngOnInit() {
//		const componentFactory = this.componentFactoryResolver.resolveComponentFactory(ListZoneContentComponent);
      
//	    const componentRef = this.zoneContentDirective.viewContainerRef.createComponent(componentFactory);
      
//      (<ZoneComponent> componentRef.instance).data = {};
	}
	
	@Input()
	set siStructure(siStructure: SiStructure) {
		this._siStructure = siStructure;
		this.applyCssClass();
	}
	
	get siStructure(): SiStructure {
		return this._siStructure;
	}
	
	get siStructureContent(): SiStructureContent|null {
		return this._siStructure.content;
	}
	
	get children(): SiStructure[] {
		return this._siStructure.getChildren();
	}
	
	getType(): SiStructureType|null {
		return this.siStructure.type;
	}

	getLabel(): string|null {
		return this.siStructure.label;
	}
	
	isMain() {
		return this.getType() == SiStructureType.MAIN_GROUP;
	}
	
//	ngDoCheck() {
//		if (this.currentSiStructure && 
//				(this.currentSiStructure !== this.siStructure)) {
//			this.structureContentDirective.viewContainerRef.clear();
//			this.currentSiStructure = null;
//		}
//		
//		if (this.currentSiStructure || !this.siStructure) {
//			return;
//		}
//		
//		this.currentSiStructure = this.siStructure;
//		this.currentSiStructure.initComponent(this.structureContentDirective.viewContainerRef,
//				this.componentFactoryResolver);
////		this.structureContentDirective.viewContainerRef.element.nativeElement.childNodes[0].classList.add('rocket-control');
//		
//		this.applyCssClass();
//	}
	
	private applyCssClass() {
		const classList = this.elRef.nativeElement.classList

		classList.remove('rocket-item');
		classList.remove('rocket-group');
		classList.remove('rocket-simple-group');
		classList.remove('rocket-main-group');
		classList.remove('rocket-light-group');
		classList.remove('rocket-panel');
		
		switch (this.getType()) {
			case SiStructureType.ITEM:
				classList.add('rocket-item');
				break;
			case SiStructureType.SIMPLE_GROUP:
			case SiStructureType.AUTONOMIC_GROUP:
				classList.add('rocket-group');
				classList.add('rocket-simple-group');
				break;
			case SiStructureType.MAIN_GROUP:
				classList.add('rocket-group');
				classList.add('rocket-main-group');
				break;
			case SiStructureType.LIGHT_GROUP:
				classList.add('rocket-group');
				classList.add('rocket-light-group');
				break;
			case SiStructureType.PANEL:
				classList.add('rocket-panel');
			default:
				break;
		}
	}
	
	get loaded(): boolean {
		return !!this.siStructure.content;
	}

}
