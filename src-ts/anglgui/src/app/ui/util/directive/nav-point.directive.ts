import { Directive, HostListener, Input, ElementRef } from '@angular/core';
import { UiNavPoint } from '../model/ui-nav-point';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { Router } from '@angular/router';

@Directive({
	selector: 'a[rocketUiNavPoint]'
})
export class NavPointDirective {

	private _uiNavPoint: UiNavPoint;
	@Input()
	callback: () => boolean;

	constructor(private router: Router, private elemRef: ElementRef) {
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
		if (this.callback && !this.callback()) {
			$event.preventDefault();
			return;
		}

		if (!this.uiNavPoint.siref) {
			return;
		}

		$event.preventDefault();
		this.router.navigateByUrl(this.uiNavPoint.url);
	}

}
