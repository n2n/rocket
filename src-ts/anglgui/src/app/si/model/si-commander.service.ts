import { Injectable } from '@angular/core';
import { SiService } from "src/app/si/model/si.service";
import { PlatformLocation } from "@angular/common";
import { Router } from "@angular/router";
import { SiLayer } from "src/app/si/model/structure/si-layer";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiInput } from "src/app/si/model/input/si-input";

@Injectable({
  providedIn: 'root'
})
export class SiCommanderService {

	constructor(private siService: SiService, private router: Router, private platformLocation: PlatformLocation) {
	}
  
	loadZone(zone: SiZone) {
		zone.content = null;
		
		this.siService.lookupSiZoneContent(zone)
				.subscribe((siZoneContent) => {
					zone.content = siZoneContent;
				});
	}
	
	navigate(url: string, layer: SiLayer) {
		if (!layer.main) {
			throw new Error('not yet implemented');
		}
		
		const baseHref = this.platformLocation.getBaseHrefFromDOM();
		
		if (!url.startsWith(baseHref)) {
			throw new IllegalSiStateError('Ref url must start with base href: ' + url);
		}
		
		this.router.navigateByUrl(url.substring(baseHref.length));
	}
	
	apiCall(callId: string, zone: SiZone, input: SiInput|null = null) {
		this.siService.apiCall(zone.content.getApiUrl(), input);
	}
	
}
