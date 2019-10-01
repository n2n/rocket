import { Component, OnInit } from '@angular/core';
import { QualifierSelectInModel } from 'src/app/ui/content/field/qualifier-select-in-model';
import { SiQualifier } from 'src/app/si/model/entity/si-qualifier';
import { SiLayer, PopupSiLayer } from 'src/app/si/model/structure/si-layer';
import { EntriesListSiComp } from 'src/app/si/model/entity/impl/basic/entries-list-si-content';
import { SiStructure } from 'src/app/si/model/structure/si-structure';

@Component({
  selector: 'rocket-qualifier-select-in-field',
  templateUrl: './qualifier-select-in-field.component.html',
  styleUrls: ['./qualifier-select-in-field.component.css']
})
export class QualifierSelectInFieldComponent implements OnInit {

	model: QualifierSelectInModel;
	siStructure: SiStructure;

	private optionsSiLayer: PopupSiLayer|null = null;

	constructor() { }

	ngOnInit() {
	}

	remove(siQualifier: SiQualifier) {
		const values = this.model.getValues();

		const index = values.indexOf(siQualifier);
		if (index > -1) {
			values.splice(index, 1);
		}
		this.model.setValues(values);
	}

	openOptions() {
		if (this.optionsSiLayer) {
			return;
		}

		const siZone = this.siStructure.getZone();

		this.optionsSiLayer = siZone.layer.container.createLayer();
		this.optionsSiLayer.onDispose(() => {
			this.optionsSiLayer = null;
		});

		const comp = new EntriesListSiComp(this.model.getApiUrl(), 30);

		this.optionsSiLayer.pushZone(null).model = {
			title: 'Some Title',
			breadcrumbs: [],
			structureModel: comp,
			controls: []
		};

		comp.qualifierSelection = {
			min: this.model.getMin(),
			max: this.model.getMax(),
			selectedQualfiers: this.model.getValues(),

			done: () => {
				this.model.setValues(comp.qualifierSelection.selectedQualfiers);
				this.optionsSiLayer.dispose();
			},

			cancel: () => {
				this.optionsSiLayer.dispose();
			}
		};
	}
}
