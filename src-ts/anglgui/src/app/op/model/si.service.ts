import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from "rxjs";
import { map } from "rxjs/operators";
import { Extractor, ObjectMissmatchError } from "src/app/util/mapping/extractor";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { ListSiZoneContent } from "src/app/si/model/structure/impl/list-si-zone-content";
import { DlSiZoneContent } from "src/app/si/model/structure/impl/dl-si-zone-content";
import { SiFactory } from "src/app/si/build/si-factory";
import { Router } from "@angular/router";
import { PlatformLocation } from "@angular/common";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";

@Injectable({
  providedIn: 'root'
})
export class SiService {
	
	constructor(private httpClient: HttpClient, private router: Router, private platformLocation: PlatformLocation) { 
	}
	  
	lookupSiZoneContent(url: string): Observable<SiZoneContent> {
		return this.httpClient.get<any>(url)
	            .pipe(map((data: any) => {
	                return this.createSiZoneContent(data);
	            }));
	}
	  
	private createSiZoneContent(data: any): SiZoneContent {
		return SiFactory.createZoneContent(data);
	}
	
	navigate(url: string) {
		const baseHref = this.platformLocation.getBaseHrefFromDOM();
		
		if (!url.startsWith(baseHref)) {
			throw new IllegalSiStateError('Ref url must start with base href: ' + url);
		}
		
		this.router.navigateByUrl(url.substring(baseHref.length));
	}
	
}


