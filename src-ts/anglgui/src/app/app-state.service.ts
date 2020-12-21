import { Injectable } from '@angular/core';
import { User } from './op/user/bo/user';

@Injectable({
	providedIn: 'root'
})
export class AppStateService {

	localeId = 'de_CH';
	user: User;
	assetsUrl: string;

	constructor() { }
}
