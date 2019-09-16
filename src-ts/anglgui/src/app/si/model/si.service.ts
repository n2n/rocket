import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { Router } from '@angular/router';
import { PlatformLocation } from '@angular/common';
import { IllegalSiStateError } from 'src/app/si/model/illegal-si-state-error';
import { SiComp } from 'src/app/si/model/entity/si-comp';
import { SiEntryInput } from 'src/app/si/model/input/si-entry-input';
import { SiInput } from 'src/app/si/model/input/si-input';
import { SiResultFactory } from 'src/app/si/build/si-result-factory';
import { SiResult } from 'src/app/si/model/control/si-result';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { SiApiFactory } from 'src/app/si/build/si-api-factory';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { SiContentFactory } from 'src/app/si/build/si-factory';
import { SiValRequest } from './api/si-val-request';
import { SiValResponse } from './api/si-val-response';

@Injectable({
  providedIn: 'root'
})
export class SiService {

	constructor(private httpClient: HttpClient) {
	}

	lookupSiContent(zone: SiZone, url: string): Observable<SiComp> {
		return this.httpClient.get<any>(url)
				.pipe(map((data: any) => {
					return this.createSiContent(data, zone);
			}));
	}

	private createSiContent(data: any, zone: SiZone): SiComp {
		return new SiContentFactory(zone).createContent(data);
	}

	entryControlCall(apiUrl: string, callId: object, entryId: string, entryInputs: SiEntryInput[]): Observable<any> {
		const formData = new FormData();
		formData.append('callId', JSON.stringify(callId));
		formData.append('siEntryId', entryId);
//        formData.append('inputMap', JSON.stringify(entryInput));

		const params = new HttpParams();

		const options = {
			params,
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

		for (const [name, param] of input.toParamMap()) {
			formData.append(name, param);
		}

		const params = new HttpParams();

		const options = {
			params,
			reportProgress: true
		};

		return this.httpClient.post<any>(apiUrl + '/execcontrol', formData, options)
// 		        .pipe(map(data => {
// 		            if (data.errors) {
// 		                throw data.errors;
// 		            }
//
// 		            return data.expert;
// 		        }))
		 		.pipe(map(data => {
					return SiResultFactory.createResult(data);
				}));
	}

	fieldCall(apiUrl: string, apiCallId: object, data: object, uploadMap: Map<string, Blob>): Observable<any> {
		const formData = new FormData();
		formData.append('apiCallId', JSON.stringify(apiCallId));
		formData.append('data', JSON.stringify(data));

		for (const [name, param] of uploadMap) {
			if (formData.has('name')) {
				throw new IllegalSiStateError('Error illegal paramName ' + name);
			}

			formData.append(name, param);
		}

		const httpParams = new HttpParams();

		const options = {
			httpParams,
			reportProgress: true
		};

		return this.httpClient.post<any>(apiUrl + '/callfield', formData, options)
		 		.pipe(map(data => {
					return new Extractor(data).reqObject('data');
				}));
	}

	apiGet(apiUrl: string, getRequest: SiGetRequest, zone: SiZone): Observable<SiGetResponse> {
		return this.httpClient
				.post<any>(apiUrl + '/get', getRequest)
				.pipe(map(data => {
					return new SiApiFactory(zone).createGetResponse(data, getRequest);
				}));
	}

	apiVal(apiUrl: string, valRequest: SiValRequest, zone: SiZone): Observable<SiValResponse> {
		return this.httpClient
				.post<any>(apiUrl + '/val', valRequest)
				.pipe(map(data => {
					return new SiApiFactory(zone).createValResponse(data, valRequest);
				}));
	}


}
