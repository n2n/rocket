
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from "@angular/core";
import { ListZoneContentComponent } from "src/app/ui/content/zone/comp/list-zone-content/list-zone-content.component";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

export class ListSiZone implements SiZone {
	private pages = new Map<number, SiPage>();
	private _count: number|null = null;
	private _fieldDeclarations: SiFieldDeclaration[]|null = null;
	
	constructor(public apiUrl: string, public pageSize: number) {
		
	}
	
	setup(fieldDeclarations: SiFieldDeclaration[], count: number) {
		this._fieldDeclarations = fieldDeclarations;
		this._count = count;
	}
	
	private ensureSetup() {
		if (this._fieldDeclarations && this._count != null) return;
		
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
	
	get fieldDeclarations(): SiFieldDeclaration[] {
		this.ensureSetup();
		
		return <SiFieldDeclaration[]> this._fieldDeclarations;
	}
	
	set count(count: number) {
		this._count = count;
	}
	
	get count(): number {
		this.ensureSetup();
		
		return <number> this._count;
	}
	
	get pagesNum(): number {
		return Math.ceil(this.count / this.pageSize);
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