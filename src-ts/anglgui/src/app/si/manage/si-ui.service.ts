import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiInput } from 'src/app/si/model/input/si-input';
import { SiValueBoundary } from 'src/app/si/model/content/si-value-boundary';
import { Observable, Subject } from 'rxjs';
import { SiService } from './si.service';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { SiCommandError } from '../util/si-command-error';
import { UiLayer } from 'src/app/ui/structure/model/ui-layer';
import { SiDirective } from './si-control-result';
import { SiControlBoundary } from '../model/control/si-control-boundary';
import { PlatformService } from 'src/app/util/nav/platform.service';
import { SiNavPoint } from '../model/control/si-nav-point';
import { IllegalStateError } from '../../util/err/illegal-state-error';
import { SiApiCallResponse } from '../model/api/si-api-call-response';

@Injectable({
	providedIn: 'root'
})
export class SiUiService {

	constructor(readonly service: SiService, private router: Router,
			private platformService: PlatformService) {
	}

	loadZone(zone: UiZone, force: boolean): void {
		if (!zone.url) {
			throw new SiCommandError('Zone contains no url.');
		}

		if (!force && zone.structure) {
			return;
		}

		zone.reset();

		this.service.lookupZone(zone);
	}

	navigateByUrl(url: string, layer: UiLayer|null): void {
		this.navigateByRouterUrl(this.platformService.routerUrl(url), layer);
	}

	navigateByRouterUrl(url: string, layer: UiLayer|null): void {
		if (layer && !layer.main) {
			this.loadZone(layer.pushRoute(null, url).zone, true);
			return;
		}

		this.router.navigateByUrl(url);
	}

	navigateBack(layer: UiLayer, fallbackUrl: string|null = null): void {
		if (layer.previousRoute && layer.previousRoute.zone.url) {
			this.navigateByRouterUrl(layer.previousRoute.zone.url, layer);
		}

		if (fallbackUrl) {
			this.navigateByUrl(fallbackUrl, layer);
			return;
		}

		if (!layer.main) {
			layer.dispose();
		}
	}

	// execEntryControl(apiUrl: string, callId: object, entry: SiEntry, includeInput: boolean, uiLayer: UiLayer): Observable<void> {
	// 	if (!entry.qualifier.identifier.id) {
	// 		throw new IllegalSiStateError('Entry control cannnot be executed on new entry.');
	// 	// 	const entryInputs: SiEntryInput[] = [];
	// 	const entries: SiEntry[] = [];
	// 	if (includeInput) {
	// 		entryInputs.push(entry.readInput());
	// 		entries.push(entry);
	// 	}

	// 	const obs = this.service.entryControlCall(apiUrl, callId, entry.qualifier.identifier.id, entryInputs);

	// 	const subject =	new Subject<void>();
	// 	obs.subscribe((result) => {
	// 		this.handleControlResult(result, entries, uiLayer);
	// 		subject.next()
	// 		subject.complete();
	// 	});

	// 	return obs;
	// }

	// execSelectionControl(apiUrl: string, callId: object, controlBoundary: SiControlBoundary, entries: SiEntry[],
	// 		includeInput: boolean, uiLayer: UiLayer): Observable<void> {
	// 	throw new Error('not yet implemented');
	// // 	const entryIds: string[] = [];
	// // 	const entryInputs: SiEntryInput[] = [];

	// // 	for (const entry of entries) {
	// // 		if (!entry.qualifier.id) {
	// // 			throw new IllegalSiStateError('Selection control cannnot be executed on new entry.');
	// // 		}

	// // 		entryIds.push(entry.qualifier.id);

	// // 		if (includeInput) {
	// // 			entryInputs.push(entry.readInput());
	// // 		}
	// // 	}

	// // 	const obs = this.service.selectionControlCall(apiUrl, callId, entryIds, entryInputs);

	// // 	obs.subscribe((result) => {
	// // 		this.handleResult(result, entries);
	// // 	});

	// // 	return obs.pipe(map((result) => {
	// // 		return;
	// // 	}));
	// }

	execControl(maskId: string|null, entryId: string|null, controlName: string, controlBoundary: SiControlBoundary,
			includeInput: boolean, uiLayer: UiLayer): Observable<void> {
		let input: SiInput|null = null;

		const valueBoundaries: SiValueBoundary[] = [];
		if (includeInput) {
			input = new SiInput(controlBoundary.getBoundDeclaration());
			for (const valueBoundary of controlBoundary.getBoundValueBoundaries()) {
				// if (valueBoundary.style.readOnly) {
				// 	continue;
				// }

				valueBoundaries.push(valueBoundary);
				input.valueBoundaryInputs.push(valueBoundary.readInput());
			}
		}

		const apiUrl = controlBoundary.getBoundApiUrl();
		IllegalStateError.assertTrue(apiUrl !== null);

		const obs = this.service.controlCall(apiUrl!, maskId, entryId, controlName, input);

		const subject = new Subject<void>();
		obs.subscribe((result) => {
			this.handleApiCallResponse(result, valueBoundaries, uiLayer);
			subject.next();
			subject.complete();
		});

		return subject;
	}

	private handleApiCallResponse(apiCallResponse: SiApiCallResponse, inputEntries: SiValueBoundary[], uiLayer: UiLayer): void {
		if (apiCallResponse.inputResult) {
			this.replaceEntries(apiCallResponse.inputResult.valueBoundaries, inputEntries);
		}

		switch (apiCallResponse.callResponse?.directive) {
			case SiDirective.REDIRECT:
				this.navigateByNavPoint(apiCallResponse.callResponse.navPoint!, uiLayer);
				break;
			case SiDirective.REDIRECT_BACK:
				this.navigateBack(uiLayer, apiCallResponse.callResponse.navPoint!.url);
				break;
		}
	}

	private navigateByNavPoint(navPoint: SiNavPoint, uiLayer: UiLayer): void {
		if (navPoint.siref) {
			this.navigateByUrl(navPoint.url, uiLayer);
		} else {
			window.location.href = navPoint.url;
		}
	}

	private replaceEntries(errorEntries: Map<string, SiValueBoundary>, valueBoundaries: SiValueBoundary[]): void {
		if (valueBoundaries.length === 0) {
			return;
		}

		for (const [key, errorEntry] of errorEntries) {
			if (!valueBoundaries[Number(key)]) {
				throw new IllegalSiStateError('Unknown entry key ' + key);
			}

			valueBoundaries[0].replace(errorEntry);
		}
	}

}
