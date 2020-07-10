import { Injectable } from '@angular/core';

@Injectable({
	providedIn: 'root'
})
export class AppStateService {

	localeId = 'de_CH';

	constructor() { }
}
