
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { ListZoneContentComponent } from "src/app/ui/content/zone/comp/list-zone-content/list-zone-content.component";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { SiCompactDeclaration } from "src/app/si/model/structure/si-compact-declaration";
import { SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiZoneError } from "src/app/si/model/structure/si-zone-error";
import { SiCommanderService } from "src/app/si/model/si-commander.service";

export class ListSiZoneContent implements SiZoneContent, SiStructureContent {
	private pages = new Map<number, SiPage>();
	public size: number|null = null;
	public compactDeclaration: SiCompactDeclaration|null = null;
	
	constructor(public apiUrl: string, public pageSize: number, public zone: SiZone) {
	}
	
	getZone(): SiZone {
		return this.zone;
	}
	
	getApiUrl(): string {
		return this.apiUrl;
	}
	
	getEntries(): SiEntry[] {
		throw new Error("Method not implemented.");
    }
	
    getSelectedEntries(): SiEntry[] {
        throw new Error("Method not implemented.");
    }
    
    getZoneErrors(): SiZoneError[] {
        return [];
    }
    
    get setup(): boolean {
    	return !!(this.compactDeclaration && this.size);
    }
	
	private ensureSetup() {
		if (this.setup) return;
		
		throw new IllegalSiStateError('ListSiZone not set up.');
	}
	
	putPage(page: SiPage) {
		if (page.number > this.pagesNum) {
			throw new IllegalSiStateError('Page num to high.');
		}
		
		this.pages.set(page.number, page);
	}
	
	constainsPageNo(number: number): boolean {
		return this.pages.has(number);
	}
	
	getPageByNo(no: number): SiPage {
		if (this.constainsPageNo(no)) {
			return <SiPage> this.pages.get(no);
		}
		
		throw new IllegalSiStateError('Unknown page with no: ' + no);
	}
	
	get pagesNum(): number {
		this.ensureSetup();
		
		return Math.ceil(<number> this.size / this.pageSize);
	}
	
	applyTo(structure: SiStructure) {
		structure.content = this;
	}
	
	reload() {
	}
	
	initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver,
			commanderService: SiCommanderService): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(ListZoneContentComponent);
	    
	    const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.model = this;
	    componentRef.instance.siService = commanderService.service;
	    
	    return componentRef;
	}
}

export class SiPage {
	constructor(readonly number: number, readonly entries: SiEntry[]) {
	}
}