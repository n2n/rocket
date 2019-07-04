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
import { SiEntryInput } from "src/app/si/model/input/si-entry-input";
import { SiInput } from "src/app/si/model/input/si-input";
import { SiResultFactory } from "src/app/si/build/si-result-factory";
import { SiResult } from "src/app/si/model/control/si-result";

@Injectable({
  providedIn: 'root'
})
export class SiService {
	
	constructor(private httpClient: HttpClient) { 
	}
	  
	lookupSiZoneContent(zone: SiZone, url: string): Observable<SiZoneContent> {
		return this.httpClient.get<any>(url)
	            .pipe(map((data: any) => {
	                return this.createSiZoneContent(data, zone);
	            }));
	}
	  
	private createSiZoneContent(data: any, zone: SiZone): SiZoneContent {
		return new SiFactory(zone).createZoneContent(data);
	}
	
	
	entryControlCall(apiUrl: string, callId: object, entryId: string, entryInputs: SiEntryInput[]): Observable<any> {
		const formData = new FormData();
		formData.append('callId', JSON.stringify(callId));
		formData.append('siEntryId', entryId);
//        formData.append('inputMap', JSON.stringify(entryInput));

        const params = new HttpParams();

        const options = {
            params: params,
            reportProgress: true
        };

        return this.httpClient.post<any>(apiUrl + '/execEntryControl', formData, options)
                .pipe(map(data => {
                    if (data.errors) {
                        throw data.errors;
                    }

                    return data.expert;
                }));
    }
	
	selectionControlCall(apiUrl: string, callId: object, entryIds: string[], entryInputs: SiEntryInput[]): Observable<any> {
		throw new Error('not yet implemented');
	}
	
	controlCall(apiUrl: string, apiCallId: object, input: SiInput): Observable<SiResult> {
		const formData = new FormData();
		formData.append('apiCallId', JSON.stringify(apiCallId));
		
		for (let [name, param] of input.toParamMap()) {
			formData.append(name, param);
		}

        const params = new HttpParams();

        const options = {
            params: params,
            reportProgress: true
        };

        return this.httpClient.post<any>(apiUrl + '/execcontrol', formData, options)
//		        .pipe(map(data => {
//		            if (data.errors) {
//		                throw data.errors;
//		            }
//		
//		            return data.expert;
//		        }))
		        .pipe(map(data => {
		        	return SiResultFactory.create(data);
		        }));
	}
}
