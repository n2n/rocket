import { Component, OnInit, Input } from '@angular/core';
import { UiBreadcrumb } from '../../model/ui-zone';

@Component({
	selector: 'rocket-ui-simple-zone-container',
	templateUrl: './simple-zone-container.component.html',
	styleUrls: ['./simple-zone-container.component.css']
})
export class SimpleZoneContainerComponent implements OnInit {

	@Input()
	title: string;
	@Input()
	loading = false;
	@Input()
	breadcrumbs: UiBreadcrumb[];

	constructor() { }

	ngOnInit() {
	}

}
