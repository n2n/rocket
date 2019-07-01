import { Component, OnInit } from '@angular/core';
import { QualifierSelectInModel } from "src/app/ui/content/field/qualifier-select-in-model";
import { SiQualifier } from "src/app/si/model/content/si-qualifier";
import { SiLayer } from "src/app/si/model/structure/si-layer";

@Component({
  selector: 'rocket-qualifier-select-in-field',
  templateUrl: './qualifier-select-in-field.component.html',
  styleUrls: ['./qualifier-select-in-field.component.css']
})
export class QualifierSelectInFieldComponent implements OnInit {

	model: QualifierSelectInModel;

	private optionsSiLayer: SiLayer|null = null;

	constructor() { }
	
	ngOnInit() {
	}
	
	remove(siQualifier: SiQualifier) {
		const values = this.model.getValues();
		
		const index = values.indexOf(siQualifier);
		if (index > -1) {
			values.splice(0, 1);
		}
	}
	
	openOptions() {
		const siZone = this.model.getSiZone();
		this.optionsSiLayer = siZone.layer.container.createLayer();
	}
}
