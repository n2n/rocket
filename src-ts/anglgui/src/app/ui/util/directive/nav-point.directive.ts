import { Directive, HostListener, Input, ElementRef, OnInit } from '@angular/core';
import { UiZone } from '../../structure/model/ui-zone';
import { UiNavPoint } from '../model/ui-nav-point';
import { SiUiService } from 'src/app/si/manage/si-ui.service';

@Directive({
	selector: 'a[rocketUiNavPoint]'
})
export class NavPointDirective {

	private _uiNavPoint: UiNavPoint;
	@Input()
	uiZone: UiZone;

	constructor(private siUiService: SiUiService, private elemRef: ElementRef) {
	}

	@Input()
	set uiNavPoint(uiNavPoint: UiNavPoint) {
		this._uiNavPoint = uiNavPoint;

		this.elemRef.nativeElement.setAttribute('href', uiNavPoint.url);
	}

	get uiNavPoint(): UiNavPoint {
		return this._uiNavPoint;
	}

	@HostListener('click', ['$event'])
	onClick($event: Event) {
		if (!this.uiNavPoint.siref) {
			return;
		}

		$event.preventDefault();
		this.siUiService.navigate(this.uiNavPoint.url, this.uiZone.layer);
	}

}
