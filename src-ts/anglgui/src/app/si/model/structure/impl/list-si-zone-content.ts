
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { ListZoneContentComponent } from "src/app/ui/content/zone/comp/list-zone-content/list-zone-content.component";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { SiCompactDeclaration } from "src/app/si/model/structure/si-compact-declaration";

export class ListSiZoneContent implements SiZoneContent {
	private pages = new Map<number, SiPage>();
	public size: number|null = null;
	
	constructor(public apiUrl: string, public pageSize: number,
			public compactDeclaration: SiCompactDeclaration|null) {
	}
	
	getApiUrl(): string {
		return this.apiUrl;
	}
	
	private ensureSetup() {
		if (this.compactDeclaration && this.size) return;
		
		throw new IllegalSiStateError('ListSiZone not set up.')
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
	
	getPageByNo(no: number) {
		if (this.constainsPageNo(no)) {
			return this.pages.get(no);
		}
		
		throw new IllegalSiStateError('Unknown page with no: ' + no);
	}
	
	get pagesNum(): number {
		this.ensureSetup();
		
		return Math.ceil(<number> this.size / this.pageSize);
	}
	
	initComponent(viewContainerRef: ViewContainerRef, 
			componentFactoryResolver: ComponentFactoryResolver): ComponentRef<ListZoneContentComponent> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(ListZoneContentComponent);
	    
	    const componentRef = viewContainerRef.createComponent(componentFactory);
	    
	    componentRef.instance.listSiZone = this;
	    
	    return componentRef;
	}
}

export class SiPage {
	constructor(readonly number: number, readonly entries: SiEntry[]) {
	}
}