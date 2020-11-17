import { Injectable } from '@angular/core';
import { PlatformLocation } from '@angular/common';
import { IllegalStateError } from '../err/illegal-state-error';

@Injectable({
providedIn: 'root'
})
export class PlatformService {

constructor(private platformLocation: PlatformLocation) { }

routerUrl(url: string): string {
const baseHref = this.platformLocation.getBaseHrefFromDOM();

if (!url.startsWith(baseHref)) {
throw new IllegalStateError('Ref url must start with base href: ' + url);
}

return url.substring(baseHref.length);
}
}
