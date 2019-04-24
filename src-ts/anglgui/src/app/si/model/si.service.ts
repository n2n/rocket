import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
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
	
	constructor(private httpClient: HttpClient) { 
	}
	  
	lookupSiZoneContent(zone: SiZone): Observable<SiZoneContent> {
		return this.httpClient.get<any>(zone.url)
	            .pipe(map((data: any) => {
	                return this.createSiZoneContent(data, zone);
	            }));
	}
	  
	private createSiZoneContent(data: any, zone: SiZone): SiZoneContent {
		return new SiFactory(zone).createZoneContent(data);
	}
	
	entryControlCall(apiUrl: string, ) {
		const formData = new FormData();
        formData.append('upload', file);

        const params = new HttpParams();

        const options = {
            params: params,
            reportProgress: true
        };

        return this.httpClient.post<any>(apiUrl + '/entryapi/expert/avatar', formData, options)
                .pipe(map(data => {
                    if (data.errors) {
                        throw data.errors;
                    }

                    return <Expert> data.expert;
                }));
    }
}
