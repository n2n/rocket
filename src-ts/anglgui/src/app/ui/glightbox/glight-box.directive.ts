import { AfterViewInit, Directive, ElementRef, HostListener, Input, OnDestroy } from '@angular/core';
import { GlightBoxService } from './glight-box.service';

@Directive({
  selector: '[rocketUiGlightBox]'
})
export class GlightBoxDirective implements AfterViewInit, OnDestroy {

	private elemRef: ElementRef;
	private glightBoxService: GlightBoxService;
	
	@Input()
	glightboxEnabled = true;
	
	
	constructor(elemRef: ElementRef, glightBoxService: GlightBoxService) {
		this.elemRef = elemRef;
		this.glightBoxService = glightBoxService;
	}


	ngAfterViewInit() {
		if (this.glightboxEnabled) {
			this.glightBoxService.registerElement(this.elemRef.nativeElement);
		}
	}

	ngOnDestroy(): void {
		this.glightBoxService.unregisterElement(this.elemRef.nativeElement);
	}
	
	@HostListener('click', ['$event'])
	onClick(btn) {
		if (this.glightboxEnabled) {
			btn.preventDefault();
			this.glightBoxService.open(this.elemRef.nativeElement);
		}
	}
}
