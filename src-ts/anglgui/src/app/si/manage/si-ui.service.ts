import { Injectable } from '@angular/core';
import { PlatformLocation } from '@angular/common';
import { Router } from '@angular/router';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiInput } from 'src/app/si/model/input/si-input';
import { SiEntryInput } from 'src/app/si/model/input/si-entry-input';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { SiEntryError } from 'src/app/si/model/input/si-entry-error';
import { Observable, Subject } from 'rxjs';
import { map } from 'rxjs/operators';
import { SiService } from './si.service';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { SiCommandError } from '../util/si-command-error';
import { UiLayer } from 'src/app/ui/structure/model/ui-layer';
import { SiResult, SiDirective } from './si-result';
import { SiControlBoundry } from '../model/control/si-control-bountry';
import { SiModStateService } from '../model/mod/si-mod-state.service';

@Injectable({
	providedIn: 'root'
})
export class SiUiService {

	constructor(readonly service: SiService, private modState: SiModStateService, private router: Router,
			private platformLocation: PlatformLocation) {
	}

	loadZone(zone: UiZone, force: boolean) {
		if (!zone.url) {
			throw new SiCommandError('Zone contains no url.');
		}

		if (!force && zone.model) {
			return;
		}

		zone.model = null;

		this.service.lookupZoneModel(zone.url, zone)
				.subscribe((zoneModel) => {
					zone.model = zoneModel;
				});
	}

	navRouterUrl(url: string): string {
		const baseHref = this.platformLocation.getBaseHrefFromDOM();

		if (!url.startsWith(baseHref)) {
			throw new IllegalSiStateError('Ref url must start with base href: ' + url);
		}

		return url.substring(baseHref.length);
	}

	navigate(url: string, layer: UiLayer) {
		const baseHref = this.platformLocation.getBaseHrefFromDOM();

		if (!url.startsWith(baseHref)) {
			throw new IllegalSiStateError('Ref url must start with base href: ' + url);
		}

		this.rlNav(url.substring(baseHref.length), layer);
	}

	private rlNav(url: string, layer: UiLayer) {
		if (!layer.main) {
			const zone = layer.pushRoute(null, url).zone;
			this.loadZone(zone, false);
			return;
		}

		this.router.navigateByUrl(url);
	}

	navigateBack(layer: UiLayer, fallbackUrl: string|null = null) {
		if (layer.previousRoute && layer.previousRoute.zone.url) {
			this.rlNav(layer.previousRoute.zone.url, layer);
		}

		if (fallbackUrl) {
			this.navigate(fallbackUrl, layer);
			return;
		}

		if (!layer.main) {
			layer.dispose();
		}
	}

	execEntryControl(apiUrl: string, callId: object, entry: SiEntry, includeInput: boolean, uiLayer: UiLayer): Observable<void> {
		if (!entry.qualifier.identifier.id) {
			throw new IllegalSiStateError('Entry control cannnot be executed on new entry.');
		}

		const entryInputs: SiEntryInput[] = [];
		const entries: SiEntry[] = [];
		if (includeInput) {
			entryInputs.push(entry.readInput());
			entries.push(entry);
		}

		const obs = this.service.entryControlCall(apiUrl, callId, entry.qualifier.identifier.id, entryInputs);

		const subject =  new Subject<void>();
		obs.subscribe((result) => {
			this.handleControlResult(result, entries, uiLayer);
			subject.next()
			subject.complete();
		});

		return obs;
	}

	execSelectionControl(apiUrl: string, callId: object, controlBoundry: SiControlBoundry, entries: SiEntry[],
			includeInput: boolean, uiLayer: UiLayer): Observable<void> {
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

	execControl(apiUrl: string, callId: object, controlBoundry: SiControlBoundry, includeInput: boolean,
			uiLayer: UiLayer): Observable<void> {
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

		const subject =  new Subject<void>();
		obs.subscribe((result) => {
			this.handleControlResult(result, entries, uiLayer);
			subject.next()
			subject.complete();
		});

		return subject;
	}

	private handleControlResult(result: SiResult, inputEntries: SiEntry[], uiLayer: UiLayer) {
		if (inputEntries.length > 0) {
			this.handleEntryErrors(result.entryErrors, inputEntries);
		}

		switch (result.directive) {
			case SiDirective.REDIRECT:
				this.navigate(result.navPoint.url, uiLayer);
				break;
			case SiDirective.REDIRECT_BACK:
				this.navigateBack(uiLayer, result.navPoint.url);
				break;
		}

		this.modState.pushModEvent(result.modEvent);
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
