import { Component, OnInit, DoCheck, Input, ElementRef, OnDestroy, ChangeDetectionStrategy, HostBinding } from '@angular/core';
import { UiZone, UiZoneModel } from '../../model/ui-zone';
import { UiZoneError } from '../../model/ui-zone-error';
import { UiContent } from '../../model/ui-content';
import { Subscription } from 'rxjs';

@Component({
	selector: 'rocket-ui-zone',
	templateUrl: './zone.component.html',
	styleUrls: ['./zone.component.css']
})
export class ZoneComponent implements OnInit, OnDestroy {

	@Input() uiZone: UiZone;

	// uiZoneErrors: UiZoneError[] = [];

	// private subscription: Subscription;

	constructor(private elemRef: ElementRef) {
	}

	ngOnInit() {
		// this.subscription = this.uiZone.uiStructure.getZoneErrors$().subscribe((uiZoneErrors) => {
		// 	this.uiZoneErrors = uiZoneErrors;
		// });
	}

	ngOnDestroy() {
		// this.subscription.unsubscribe();
		// this.subscription = null;
	}

	get uiZoneErrors(): UiZoneError[] {
		return this.uiZone.uiStructure.getZoneErrors();
	}

	get asideCommandUiContents(): UiContent[] {
		if (!this.uiZone.uiStructure.model) {
			return [];
		}
		return this.uiZone.uiStructure.model.getAsideContents()
	}

	get uiZoneModel(): UiZoneModel|null {
		return this.uiZone.model;
	}

	@HostBinding('class.rocket-contains-additional')
	hasUiZoneErrors() {
		return this.uiZoneErrors.length > 0;
	}

	get partialCommandUiContents(): UiContent[] {
		return this.uiZone.model.partialCommandContents || [];
	}

	get mainCommandUiContents(): UiContent[] {
		return [...this.uiZone.model.mainCommandContents, ...this.uiZone.uiStructure.model.getMainControlContents()];
	}


}
