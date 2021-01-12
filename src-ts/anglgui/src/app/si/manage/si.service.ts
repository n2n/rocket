import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { UiZoneModel, UiZone } from 'src/app/ui/structure/model/ui-zone';
import { map } from 'rxjs/operators';
import { SiEntryInput } from '../model/input/si-entry-input';
import { HttpParams, HttpClient } from '@angular/common/http';
import { SiInput } from '../model/input/si-input';
import { SiResult } from './si-result';
import { SiResultFactory } from '../build/si-result-factory';
import { IllegalSiStateError } from '../util/illegal-si-state-error';
import { SiGetRequest } from '../model/api/si-get-request';
import { SiGetResponse } from '../model/api/si-get-response';
import { SiApiFactory } from '../build/si-api-factory';
import { SiValRequest } from '../model/api/si-val-request';
import { SiValResponse } from '../model/api/si-val-response';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiUiFactory } from '../build/si-ui-factory';
import { SiSortRequest } from '../model/api/si-sort-request';
import { SiModStateService } from '../model/mod/model/si-mod-state.service';

@Injectable({
	providedIn: 'root'
})
export class SiService {

	constructor(private httpClient: HttpClient, private modState: SiModStateService, private injector: Injector) {
	}

	lookupZoneModel(url: string, uiZone: UiZone): Observable<UiZoneModel> {
		return this.httpClient.get<any>(url)
				.pipe(map((data: any) => {
					return new SiUiFactory(this.injector).createZoneModel(data, uiZone.layer);
				}));
	}

// 	entryControlCall(apiUrl: string, callId: object, entryId: string, entryInputs: SiEntryInput[]): Observable<any> {
// 		const formData = new FormData();
// 		formData.append('callId', JSON.stringify(callId));
// 		formData.append('siEntryId', entryId);
// // 		formData.append('inputMap', JSON.stringify(entryInput));

// 		const params = new HttpParams();

// 		const options = {
// 			params,
// 			reportProgress: true
// 		};

// 		return this.httpClient.post<any>(apiUrl + '/execEntryControl', formData, options)
// 				.pipe(map(data => {
// 					if (data.errors) {
// 						throw data.errors;
// 					}

// 					return data.expert;
// 				}));
// 	}

	selectionControlCall(): Observable<any> {
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
				.pipe(map(data => {
					const result = SiResultFactory.createResult(data);
					this.handleResult(result);
					return result;
				}));
	}

	fieldCall(apiUrl: string, apiCallId: object, data: object, uploadMap: Map<string, Blob>): Observable<any> {
		const formData = new FormData();
		formData.append('apiCallId', JSON.stringify(apiCallId));
		formData.append('data', JSON.stringify(data));

		for (const [name, param] of uploadMap) {
			if (formData.has(name)) {
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
		 		.pipe(map(responseData => {
					return new Extractor(responseData).nullaObject('data');
				}));
	}

	apiGet(apiUrl: string, getRequest: SiGetRequest): Observable<SiGetResponse> {
		return this.httpClient
				.post<any>(apiUrl + '/get', getRequest)
				.pipe(map(data => {
					return new SiApiFactory(this.injector).createGetResponse(data, getRequest);
				}));
	}

	apiVal(apiUrl: string, valRequest: SiValRequest): Observable<SiValResponse> {
		return this.httpClient
				.post<any>(apiUrl + '/val', valRequest)
				.pipe(map(data => {
					return new SiApiFactory(this.injector).createValResponse(data, valRequest);
				}));
	}

	apiSort(apiUrl: string, sortRequest: SiSortRequest): Observable<SiResult> {
		return this.httpClient
				.post(apiUrl + '/sort', sortRequest)
				.pipe(map(data => {
					const result = SiResultFactory.createResult(data);
					this.handleResult(result);
					return result;
				}));
	}

	private handleResult(result: SiResult) {
		this.modState.pushModEvent(result.modEvent);
		this.modState.pushMessages(result.messages);
	}
}
