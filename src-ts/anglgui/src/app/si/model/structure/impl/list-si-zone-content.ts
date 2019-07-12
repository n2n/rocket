
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
import { SiPage } from "src/app/si/model/structure/impl/si-page";

export class ListSiZoneContent implements SiZoneContent, SiStructureContent {
	private pagesMap = new Map<number, SiPage>();
	private _size: number = 0;
	private _currentPageNo: number = 1;
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
    
    get pages(): SiPage[] {
    	return Array.from(this.pagesMap.values());
    }
    
    get currentPage(): SiPage {
    	return this.getPageByNo(this._currentPageNo);
    }
    
    get currentPageNo(): number {
    	this.ensureSetup();
    	return this._currentPageNo;
    }
    
    set currentPageNo(currentPageNo: number) {
    	if (currentPageNo > this.pagesNum) {
    		throw new IllegalSiStateError('CurrentPageNo too large: ' + currentPageNo);
    	}
    	
    	if (!this.getPageByNo(currentPageNo).visible) {
    		throw new IllegalSiStateError('Page not visible: ' + currentPageNo);
    	}
    	
    	this._currentPageNo = currentPageNo;
    }
    
    get size(): number {
    	return <number> this._size;
    }
    
    set size(size: number) {
    	this._size = size;
    	
    	if (!this.setup) {
    		return;
    	}
    	
    	const pagesNum = this.pagesNum;
    	
    	if (this._currentPageNo > pagesNum) {
    		this._currentPageNo = pagesNum;
    	}
    	
    	for (let pageNo of this.pagesMap.keys()) {
    		if (pageNo > pagesNum) {
    			this.pagesMap.delete(pageNo);
    		}
    	}
    }
    
    get setup(): boolean {
    	return !!(this.compactDeclaration);
    }
	
	private ensureSetup() {
		if (this.setup) return;
		
		throw new IllegalSiStateError('ListSiZone not set up.');
	}
	
	putPage(page: SiPage) {
		if (page.number > this.pagesNum) {
			throw new IllegalSiStateError('Page num to high.');
		}
		
		this.pagesMap.set(page.number, page);
	}
	
	getVisiblePages(): SiPage[] {
		return this.pages.filter((page: SiPage) => {
			return page.offsetHeight !== null;
		});
	}
	
	getLastVisiblePage(): SiPage|null {
		let lastPage: SiPage|null = null;
		for (const page of this.pagesMap.values()) {
			if (page.offsetHeight !== null && (lastPage === null || page.number > lastPage.number)) {
				lastPage = page;
			}
		}
		return lastPage;
	}
	
	getBestPageByOffsetHeight(offsetHeight: number): SiPage|null {
		let prevPage: SiPage|null = null;
		
		for (const page of this.getVisiblePages()) {
			if (prevPage === null || (prevPage.offsetHeight < offsetHeight
					&& prevPage.offsetHeight <= page.offsetHeight)) {
				prevPage = page;
				continue;
			}
			
			const bestPageDelta = offsetHeight - prevPage.offsetHeight;
			const pageDelta = page.offsetHeight - offsetHeight;
			
			if (bestPageDelta < pageDelta) {
				return prevPage;
			} else {
				return page;
			}
		}
		
		return prevPage;
	}
	
	hideAllPages() {
		for (const page of this.pagesMap.values()) {
			page.offsetHeight = null;
		}
	}
	
	containsPageNo(number: number): boolean {
		return this.pagesMap.has(number);
	}
	
	getPageByNo(no: number): SiPage {
		if (this.containsPageNo(no)) {
			return <SiPage> this.pagesMap.get(no);
		}
		
		throw new IllegalSiStateError('Unknown page with no: ' + no);
	}
	
	get pagesNum(): number {
		return Math.ceil(<number> this.size / this.pageSize) || 1;
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

