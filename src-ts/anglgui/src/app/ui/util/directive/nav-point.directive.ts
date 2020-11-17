import { Directive, HostListener, Input, ElementRef } from '@angular/core';
import { UiNavPoint } from '../model/ui-nav-point';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { Router } from '@angular/router';
import { PlatformService } from 'src/app/util/nav/platform.service';

@Directive({
	selector: 'a[rocketUiNavPoint]'
})
export class NavPointDirective {

	private _uiNavPoint: UiNavPoint;
	constructor(private router: Router, private elemRef: ElementRef,
			private platformService: PlatformService) {
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
		if (this.uiNavPoint.callback && !this.uiNavPoint.callback()) {
			$event.preventDefault();
			return;
		}

		if (!this.uiNavPoint.siref) {
			return;
		}

		$event.preventDefault();
		this.router.navigateByUrl(this.platformService.routerUrl(this.uiNavPoint.url));
	}

}
