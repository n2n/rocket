import { Injectable } from '@angular/core';
import GLightbox from 'glightbox';

@Injectable({
  providedIn: 'root'
})
export class GlightBoxService {

	public htmlElements: Array <HTMLElement> = [];
	public glightbox: GLightbox;
	
 	constructor() { }

	registerElement(htmlElement: HTMLElement) {
		this.htmlElements.push(htmlElement);
		this.renewGLightBox();
	}
	
	unregisterElement(htmlElement: HTMLElement) {
		const i = this.htmlElements.indexOf(htmlElement);
		
		if (i > -1) {
			this.htmlElements.splice(i, 1);
		}
		
		this.renewGLightBox();
	}
	
	renewGLightBox() {
		if (this.glightbox) {
			this.glightbox.destroy();
		}
		
		this.glightbox = GLightbox({
	    	elements: this.htmlElements.map(e => ({ 
			'href': (e as any).href,
            'type': 'image'}))
		});
		
	}
	
	open(htmlElement: HTMLElement) {
		const i = this.htmlElements.indexOf(htmlElement);
		
		if (i === -1) {
			throw new Error('glightbox: unregistered Element');
		}
		
		this.glightbox.openAt(i);
	}
}
