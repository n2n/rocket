import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class TranslationService {
	
	map: { [key: string]: string } = {};

	t(key: string): string {
		return this.map[key] || key;
	}
  
}
