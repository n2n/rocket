import { Component, OnInit } from '@angular/core';
import { SiEntryQualifier } from 'src/app/si/model/content/si-qualifier';
import { QualifierSelectInModel } from '../qualifier-select-in-model';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { PopupUiLayer } from 'src/app/ui/structure/model/ui-layer';
import { EntriesListSiComp } from 'src/app/si/model/comp/impl/model/entries-list-si-comp';


@Component({
	selector: 'rocket-qualifier-select-in-field',
	templateUrl: './qualifier-select-in-field.component.html',
	styleUrls: ['./qualifier-select-in-field.component.css']
})
export class QualifierSelectInFieldComponent implements OnInit {

	model: QualifierSelectInModel;
	uiStructure: UiStructure;

	private optionsUiLayer: PopupUiLayer|null = null;

	constructor() { }

	ngOnInit() {
	}

	remove(siQualifier: SiEntryQualifier) {
		const values = this.model.getValues();

		const index = values.indexOf(siQualifier);
		if (index > -1) {
			values.splice(index, 1);
		}
		this.model.setValues(values);
	}

	openOptions() {
		if (this.optionsUiLayer) {
			return;
		}

		const uiZone = this.uiStructure.getZone();

		this.optionsUiLayer = uiZone.layer.container.createLayer();
		this.optionsUiLayer.onDispose(() => {
			this.optionsUiLayer = null;
		});

		const comp = new EntriesListSiComp(this.model.getApiUrl(), 30);

		this.optionsUiLayer.pushZone(null).model = {
			title: 'Some Title',
			breadcrumbs: [],
			structureModel: comp.createUiStructureModel(),
			controls: []
		};

		comp.qualifierSelection = {
			min: this.model.getMin(),
			max: this.model.getMax(),
			selectedQualfiers: this.model.getValues(),

			done: () => {
				this.model.setValues(comp.qualifierSelection.selectedQualfiers);
				this.optionsUiLayer.dispose();
			},

			cancel: () => {
				this.optionsUiLayer.dispose();
			}
		};
	}
}
