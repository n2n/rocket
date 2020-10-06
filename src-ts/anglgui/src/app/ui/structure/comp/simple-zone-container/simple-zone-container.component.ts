import { Component, OnInit, Input } from '@angular/core';

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

	constructor() { }

	ngOnInit() {
	}

}
