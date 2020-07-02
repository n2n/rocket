import { Injectable } from '@angular/core';
import { PlatformLocation } from '@angular/common';
import { Router } from '@angular/router';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiInput } from 'src/app/si/model/input/si-input';
import { SiEntryInput } from 'src/app/si/model/input/si-entry-input';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { SiEntryError } from 'src/app/si/model/input/si-entry-error';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { SiService } from './si.service';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { SiCommandError } from '../util/si-command-error';
import { UiLayer } from 'src/app/ui/structure/model/ui-layer';
import { SiResult, SiDirective } from './si-result';
import { SiControlBoundry } from '../model/control/si-control-bountry';

@Injectable({
	providedIn: 'root'
})
export class SiUiService {

	constructor(readonly service: SiService, private router: Router, private platformLocation: PlatformLocation) {
	}

	loadZone(zone: UiZone) {
		if (!zone.url) {
			throw new SiCommandError('Zone contains no url.');
		}

		zone.model = null;

		this.service.lookupZoneModel(zone.url, zone)
				.subscribe((zoneModel) => {
					zone.model = zoneModel;
				});
	}

	navigateBack(layer: UiLayer, fallbackUrl: string|null = null) {
		
	}

	navigateForward(layer: UiLayer, fallbackUrl: string|null = null) {

	}

	navRouterUrl(url: string): string {
		const baseHref = this.platformLocation.getBaseHrefFromDOM();

		if (!url.startsWith(baseHref)) {
			throw new IllegalSiStateError('Ref url must start with base href: ' + url);
		}

		return url.substring(baseHref.length);
	}

	navigate(url: string, layer: UiLayer) {
		if (!layer.main) {
			layer.currentZone
			throw new Error('not yet implemented');
		}

		const baseHref = this.platformLocation.getBaseHrefFromDOM();

		if (!url.startsWith(baseHref)) {
			throw new IllegalSiStateError('Ref url must start with base href: ' + url);
		}

		this.router.navigateByUrl(url.substring(baseHref.length));
	}

	execEntryControl(apiUrl: string, callId: object, entry: SiEntry, includeInput: boolean): Observable<void> {
		if (!entry.qualifier.identifier.id) {
			throw new IllegalSiStateError('Entry control cannnot be executed on new entry.');
		}

		const entryInputs: SiEntryInput[] = [];
		if (includeInput) {
			entryInputs.push(entry.readInput());
		}

		const obs = this.service.entryControlCall(apiUrl, callId, entry.qualifier.identifier.id, entryInputs);

		obs.subscribe((result) => {
// 			this.handleResult(result);
		});

		return obs.pipe(map((result) => {
			return;
		}));
	}

	execSelectionControl(apiUrl: string, callId: object, controlBoundry: SiControlBoundry, entries: SiEntry[], includeInput: boolean): Observable<void> {
		throw new Error('not yet implemented');
	// 	const entryIds: string[] = [];
	// 	const entryInputs: SiEntryInput[] = [];

	// 	for (const entry of entries) {
	// 		if (!entry.qualifier.id) {
	// 			throw new IllegalSiStateError('Selection control cannnot be executed on new entry.');
	// 		}

	// 		entryIds.push(entry.qualifier.id);

	// 		if (includeInput) {
	// 			entryInputs.push(entry.readInput());
	// 		}
	// 	}

	// 	const obs = this.service.selectionControlCall(apiUrl, callId, entryIds, entryInputs);

	// 	obs.subscribe((result) => {
	// 		this.handleResult(result, entries);
	// 	});

	// 	return obs.pipe(map((result) => {
	// 		return;
	// 	}));
	}

	execControl(apiUrl: string, callId: object, controlBoundry: SiControlBoundry, includeInput: boolean): Observable<void> {
		const input = new SiInput();

		if (!includeInput) {
			throw new Error('not yet implemented');
		}

		const entries: SiEntry[] = [];
		for (const entry of controlBoundry.getEntries()) {
			if (entry.readOnly) {
				continue;
			}

			entries.push(entry);
			input.entryInputs.push(entry.readInput());
		}

		const obs = this.service.controlCall(apiUrl, callId, input);

		return new Observable<void>((observer) => {
			obs.subscribe((result) => {
				this.handleResult(result, entries);
				observer.next();
				observer.complete();
			});
		});
	}

	private handleResult(result: SiResult, inputEntries: SiEntry[]) {
		if (inputEntries.length > 0) {
			this.handleEntryErrors(result.entryErrors, inputEntries);
		}

		switch (result.directive) {
			case SiDirective.REDIRECT:

				break;
			case SiDirective.REDIRECT_BACK:
				this.navigateBack();
				break;
		}
	}

	private handleEntryErrors(entryErrors: Map<string, SiEntryError>, entries: SiEntry[]) {
		if (entries.length === 0) {
			return;
		}

		for (const entry of entries) {
			entry.resetError();
		}

		for (const [key, entryError] of entryErrors) {
			if (!entries[key]) {
				throw new IllegalSiStateError('Unknown entry key ' + key);
			}

			entries[0].handleError(entryError);
		}
	}

}
