import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from "rxjs";
import { map } from "rxjs/operators";
import { Extractor, ObjectMissmatchError } from "src/app/util/mapping/extractor";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiFactory } from "src/app/si/build/si-factory";
import { Router } from "@angular/router";
import { PlatformLocation } from "@angular/common";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { SiContent } from "src/app/si/model/structure/si-zone-content";
import { SiEntryInput } from "src/app/si/model/input/si-entry-input";
import { SiInput } from "src/app/si/model/input/si-input";
import { SiResultFactory } from "src/app/si/build/si-result-factory";
import { SiResult } from "src/app/si/model/control/si-result";
import { SiGetRequest } from "src/app/si/model/api/si-get-request";
import { SiApiFactory } from "src/app/si/build/si-api-factory";
import { SiGetResponse } from "src/app/si/model/api/si-get-response";

@Injectable({
  providedIn: 'root'
})
export class SiService {
	
	constructor(private httpClient: HttpClient) { 
	}
	  
	lookupSiContent(zone: SiZone, url: string): Observable<SiContent> {
		return this.httpClient.get<any>(url)
	            .pipe(map((data: any) => {
	                return this.createSiContent(data, zone);
	            }));
	}
	
	private createSiContent(data: any, zone: SiZone): SiContent {
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
		        	return SiResultFactory.createResult(data);
		        }));
	}
	
	apiGet(apiUrl: string, getRequest: SiGetRequest, zone: SiZone, zoneContent: SiContent): Observable<SiGetResponse> {
		return this.httpClient
				.post<any>(apiUrl + '/get', getRequest)
				.pipe(map(data => {
					return new SiApiFactory(zone, zoneContent).createGetResponse(data);
				}));
	}
	
	
}
