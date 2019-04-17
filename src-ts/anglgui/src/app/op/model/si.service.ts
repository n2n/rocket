import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from "rxjs";
import { map } from "rxjs/operators";
import { Extractor, ObjectMissmatchError } from "src/app/util/mapping/extractor";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { ListSiZone } from "src/app/si/model/structure/impl/list-si-zone";
import { DlSiZone } from "src/app/si/model/structure/impl/dl-si-zone";
import { SiZoneFactory } from "src/app/si/build/si-zone-factory";
import { Router } from "@angular/router";
import { PlatformLocation } from "@angular/common";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

@Injectable({
  providedIn: 'root'
})
export class SiService {
	
	constructor(private httpClient: HttpClient, private router: Router, private platformLocation: PlatformLocation) { 
	}
	  
	lookupSiZone(url: string): Observable<SiZone> {
		return this.httpClient.get<any>(url)
	            .pipe(map((data: any) => {
	                return this.createSiZone(data);
	            }));
	}
	  
	private createSiZone(data: any): SiZone {
		return SiZoneFactory.create(data);
	}
	
	navigate(url: string) {
		const baseHref = this.platformLocation.getBaseHrefFromDOM();
		
		if (!url.startsWith(baseHref)) {
			throw new IllegalSiStateError('Ref url must start with base href: ' + url);
		}
		
		this.router.navigateByUrl(url.substring(baseHref.length));
	}
	
}


