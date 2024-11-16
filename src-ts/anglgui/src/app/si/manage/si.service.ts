import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { map } from 'rxjs/operators';
import { HttpParams, HttpClient } from '@angular/common/http';
import { SiInput } from '../model/input/si-input';
import { SiCallResponse } from './si-control-result';
import { IllegalSiStateError } from '../util/illegal-si-state-error';
import { SiGetRequest } from '../model/api/si-get-request';
import { SiGetResponse } from '../model/api/si-get-response';
import { SiApiFactory } from '../build/si-api-factory';
import { SiValRequest } from '../model/api/si-val-request';
import { SiValResponse } from '../model/api/si-val-response';
import { SiSortRequest } from '../model/api/si-sort-request';
import { SiModStateService } from '../model/mod/model/si-mod-state.service';
import { SiResultFactory } from '../build/si-result-factory';
import { SiUiFactory } from '../build/si-ui-factory';
import { SiApiCall } from '../model/api/si-api-call';
import { SiApiCallResponse } from '../model/api/si-api-call-response';
import { SiControlCall } from '../model/api/si-control-call';
import { SiFieldCall } from '../model/api/si-field-call';

@Injectable({
	providedIn: 'root'
})
export class SiService {

	constructor(private httpClient: HttpClient, private modState: SiModStateService, private injector: Injector) {
	}

	lookupZone(uiZone: UiZone): Promise<void> {
		return this.httpClient.get<any>(uiZone.url!)
				.pipe(map((data: any) => {
					new SiUiFactory(this.injector).fillZone(data, uiZone);
				}))
				.toPromise();
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

	controlCall(apiUrl: string, maskId: string|null, entryId: string|null, controlName: string, input: SiInput|null): Observable<SiApiCallResponse> {
		// const formData = new FormData();
		// formData.append('controlName', controlName);
		//
		// if (input) {
		// 	for (const [name, param] of input.toParamMap()) {
		// 		formData.append(name, param);
		// 	}
		// }
		//
		// const params = new HttpParams();
		//
		// const options = {
		// 	params,
		// 	reportProgress: true
		// };

		return this.apiCall(apiUrl, new SiApiCall(input, new SiControlCall(maskId, entryId, controlName)))
				.pipe(map((r) => r));

		// return this.httpClient.post<any>(apiUrl, formData, options)
		// 		.pipe(map(data => {
		// 			const resultFactory = new SiResultFactory(this.injector, apiUrl);
		// 			const result = resultFactory.createControlResult(data, input?.declaration);
		// 			if (result.callResponse) {
		// 				this.handleCallResponse(result.callResponse);
		// 			}
		// 			return result;
		// 		}));
	}

	fieldCall(apiUrl: string, maskId: string, entryId: string|null, fieldName: string, data: object, uploadMap: Map<string, Blob>): Observable<any> {
		// const formData = new FormData();
		// formData.append('apiCallId', JSON.stringify(apiCallId));
		// formData.append('data', JSON.stringify(data));
		//
		// for (const [name, param] of uploadMap) {
		// 	if (formData.has(name)) {
		// 		throw new IllegalSiStateError('Error illegal paramName ' + name);
		// 	}
		//
		// 	formData.append(name, param);
		// }

		// const httpParams = new HttpParams();
		//
		// const options = {
		// 	httpParams,
		// 	reportProgress: true
		// };
		//
		// return this.httpClient.post<any>(apiUrl, formData, options)
		//  		.pipe(map(responseData => {
		// 			return new Extractor(responseData).nullaObject('data');
		// 		}));

		return this.apiCall(apiUrl, SiApiCall.fieldCall(new SiFieldCall(maskId, entryId, fieldName, data)), uploadMap);

	}

	apiGet(apiUrl: string, getRequest: SiGetRequest): Observable<SiGetResponse> {
		return this.httpClient
				.post<any>(apiUrl, getRequest)
				.pipe(map(data => {
					return new SiApiFactory(this.injector, apiUrl).createGetResponse(data, getRequest);
				}));
	}

	apiVal(apiUrl: string, valRequest: SiValRequest): Observable<SiValResponse> {
		return this.httpClient
				.post<any>(apiUrl, valRequest)
				.pipe(map(data => {
					return new SiApiFactory(this.injector, apiUrl).createValResponse(data, valRequest);
				}));
	}

	apiSort(apiUrl: string, sortRequest: SiSortRequest): Observable<SiCallResponse> {
		return this.apiCall(apiUrl, SiApiCall.sortCall(sortRequest))
				.pipe(map((r) => r.callResponse!));
	}

	apiCall(apiUrl: string, apiCall: SiApiCall, uploadMap: Map<string, Blob>|null = null): Observable<SiApiCallResponse> {
		const formData = new FormData();
		formData.append('call', JSON.stringify(apiCall.toJsonStruct()));

		if (uploadMap !== null) {
			for (const [name, param] of uploadMap) {
				if (formData.has(name)) {
					throw new IllegalSiStateError('Error illegal paramName ' + name);
				}

				formData.append(name, param);
			}
		}

		const httpParams = new HttpParams();

		const options = {
			httpParams,
			reportProgress: true
		};

		const resultFactory = new SiResultFactory(this.injector, apiUrl);
		return this.httpClient
				.post(apiUrl, formData, options)
				.pipe(map(data => {
					const apiCallResponse = resultFactory.createApiCallResponse(data, apiCall);
					if (apiCallResponse.callResponse) {
						this.handleCallResponse(apiCallResponse.callResponse);
					}
					return apiCallResponse;
				}));
	}

	private handleCallResponse(result: SiCallResponse): void {
		this.modState.pushModEvent(result.modEvent!);
		this.modState.pushMessages(result.messages);
	}
}
