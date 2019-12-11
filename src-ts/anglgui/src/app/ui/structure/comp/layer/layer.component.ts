import { Component, OnInit, Input } from '@angular/core';
import { UiLayer } from '../../model/ui-layer';

@Component({
	selector: 'rocket-ui-layer',
	templateUrl: './layer.component.html',
	styleUrls: ['./layer.component.css']
})
export class LayerComponent implements OnInit {
	@Input()
	uiLayer: UiLayer;

	constructor() { }

	ngOnInit() {
	}
}
