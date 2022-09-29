import {AfterViewInit, Directive, ElementRef, OnDestroy} from '@angular/core';
import {IFrameComponent, iframeResizer} from 'iframe-resizer';

@Directive({
	selector: '[resizableIframe]'
})
export class IFrameResizerDirective implements AfterViewInit, OnDestroy {
	component: IFrameComponent|null = null;

	constructor(public element: ElementRef) {
	}

	ngAfterViewInit() {
		const components = iframeResizer({
			checkOrigin: false,
			heightCalculationMethod: 'lowestElement',
			log: false
		}, this.element.nativeElement);

		/* save component reference so we can close it later */
		this.component = components && components.length > 0 ? components[0] : null;
	}

	ngOnDestroy(): void {
		if (this.component && this.component.iFrameResizer) {
			this.component.iFrameResizer.close();
		}
	}
}
