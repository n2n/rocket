import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from "rxjs";
import { map } from "rxjs/operators";
import { Extractor, ObjectMissmatchError } from "src/app/util/mapping/extractor";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { ListSiZone } from "src/app/si/model/structure/impl/list-si-zone";
import { DlSiZone } from "src/app/si/model/structure/impl/dl-si-zone";
import { SiZoneFactory } from "src/app/si/build/si-zone-factory";

@Injectable({
  providedIn: 'root'
})
export class SiService {
	
	constructor(private httpClient: HttpClient) { 
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
}


