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
import { SiControlResult, SiDirective } from './si-control-result';
import { SiControlBoundry } from '../model/control/si-control-bountry';
import { PlatformService } from 'src/app/util/nav/platform.service';
import { SiNavPoint } from '../model/control/si-nav-point';

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

	// execSelectionControl(apiUrl: string, callId: object, controlBoundry: SiControlBoundry, entries: SiEntry[],
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

	execControl(apiUrl: string, callId: object, controlBoundry: SiControlBoundry, includeInput: boolean,
			uiLayer: UiLayer): Observable<void> {
		let input: SiInput|null = null;

		const valueBoundaries: SiValueBoundary[] = [];
		if (includeInput) {
			input = new SiInput(controlBoundry.getBoundDeclaration());
			for (const valueBoundary of controlBoundry.getBoundValueBoundaries()) {
				if (valueBoundary.style.readOnly) {
					continue;
				}

				valueBoundaries.push(valueBoundary);
				input.entryInputs.push(valueBoundary.readInput());
			}
		}

		const obs = this.service.controlCall(apiUrl, controlBoundry.getBoundDeclaration().style, callId, input);

		const subject =	new Subject<void>();
		obs.subscribe((result) => {
			this.handleControlResult(result, valueBoundaries, uiLayer);
			subject.next();
			subject.complete();
		});

		return subject;
	}

	private handleControlResult(result: SiControlResult, inputEntries: SiValueBoundary[], uiLayer: UiLayer): void {
		if (result.inputError) {
			this.replaceEntries(result.inputError.errorEntries, inputEntries);
		}

		if (result.inputResult) {
			this.replaceEntries(result.inputResult.valueBoundaries, inputEntries);
		}


		switch (result.callResponse?.directive) {
			case SiDirective.REDIRECT:
				this.navigateByNavPoint(result.callResponse.navPoint!, uiLayer);
				break;
			case SiDirective.REDIRECT_BACK:
				this.navigateBack(uiLayer, result.callResponse.navPoint!.url);
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

	private replaceEntries(errorEntries: Map<string, SiValueBoundary>, entries: SiValueBoundary[]): void {
		if (entries.length === 0) {
			return;
		}

		for (const [key, errorEntry] of errorEntries) {
			if (!entries[Number(key)]) {
				throw new IllegalSiStateError('Unknown entry key ' + key);
			}

			entries[0].replace(errorEntry);
		}
	}

}
