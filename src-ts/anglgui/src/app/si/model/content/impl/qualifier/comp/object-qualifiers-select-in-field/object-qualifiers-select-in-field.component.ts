import { Component, OnInit, ElementRef, DoCheck } from '@angular/core';
import { SiEntryQualifier } from 'src/app/si/model/content/si-entry-qualifier';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { PopupUiLayer } from 'src/app/ui/structure/model/ui-layer';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { CompactExplorerSiGui } from 'src/app/si/model/gui/impl/model/compact-explorer-si-gui';
import { SimpleSiControl } from 'src/app/si/model/control/impl/model/simple-si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SiService } from 'src/app/si/manage/si.service';
import { SiModStateService } from 'src/app/si/model/mod/model/si-mod-state.service';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { ObjectQualifiersSelectInModel } from '../object-qualifiers-select-in-model';
import { SiObjectQualifier } from '../../../../si-object-qualifier';
import { SiObjectQualifierSelection } from '../../../../../gui/impl/model/si-entry-qualifier-selection';


@Component({
	selector: 'rocket-object-qualifiers-select-in-field',
	templateUrl: './object-qualifiers-select-in-field.component.html',
	styleUrls: ['./object-qualifiers-select-in-field.component.css'],
	host: {class: 'rocket-qualifier-select-in-field'}
})
export class ObjectQualifiersSelectInFieldComponent implements OnInit, DoCheck {

	model!: ObjectQualifiersSelectInModel;
	uiStructure!: UiStructure;
	pickables: Array<SiObjectQualifier>|null = null;

	private optionsUiLayer: PopupUiLayer|null = null;

	constructor(private elemRef: ElementRef, private translationService: TranslationService,
				private siService: SiService, private siModState: SiModStateService) { }

	ngOnInit() {
		if (1 === this.model.getMax()) {
			this.elemRef.nativeElement.classList.add('rocket-to-one');
		} else {
			this.elemRef.nativeElement.classList.add('rocket-to-many');
		}
	}

	ngDoCheck(): void {
		const pickables = this.model.getPickables();
		if (!pickables) {
			this.pickables = null;
			return;
		}

		this.pickables = pickables.filter(pickable => -1 === this.findValIndex(pickable));
	}

	remove(siObjectQualifier: SiObjectQualifier) {
		const values = this.model.getValues();

		const index = values.indexOf(siObjectQualifier);
		if (index > -1) {
			values.splice(index, 1);
		}
		this.model.setValues(values);
	}

	private findValIndex(siObjectQualifier: SiObjectQualifier): number {
		return this.model.getValues().findIndex(qual => qual.matchesObjectIdentifier(siObjectQualifier));
	}

	get pickingAllowed(): boolean {
		const max = this.model.getMax();

		return max === null || this.toOne ||	this.model.getValues().length < max;
	}

	get toOne(): boolean {
		return this.model.getMax() === 1;
	}

	pick(siObjectQualifier: SiObjectQualifier) {
		if (-1 !== this.findValIndex(siObjectQualifier)) {
			return;
		}

		const values = this.toOne ? [] : this.model.getValues();
		values.push(siObjectQualifier);
		this.model.setValues(values);
	}

	openOptions() {
		if (this.optionsUiLayer) {
			return;
		}

		const uiZone = this.uiStructure.getZone()!;

		this.optionsUiLayer = uiZone.layer.container.createLayer();
		this.optionsUiLayer.onDispose(() => {
			this.optionsUiLayer = null;
		});

		const comp = new CompactExplorerSiGui(30, this.model.getSiFrame(), this.siService, this.siModState);

		const popupUiZone = this.optionsUiLayer.pushRoute(null, null).zone;

		const qualifierSelection = new SiObjectQualifierSelection(this.model.getMin(), this.model.getMax(),
				this.model.getValues());

		popupUiZone.title = 'Some Title';
		popupUiZone.breadcrumbs = [];
		popupUiZone.structure = new UiStructure(UiStructureType.SIMPLE_GROUP, null, comp.createUiStructureModel());
		popupUiZone.mainCommandContents = this.createSiControls(qualifierSelection)
				.map(control => control.createUiContent(() => popupUiZone));

		comp.qualifierSelection = qualifierSelection;
	}

	private createSiControls(qualifierSelection: SiObjectQualifierSelection) {
		return [
			new SimpleSiControl(
					new SiButton(this.translationService.translate('common_select_label'), 'btn btn-primary rocket-important', 'far fa-circle'),
					() => {
						this.model.setValues(qualifierSelection.selectedQualifiers);
						this.optionsUiLayer?.dispose();
					}),
			new SimpleSiControl(
					new SiButton(this.translationService.translate('common_cancel_label'), 'btn btn-secondary', 'far fa-circle'),
					() => {
						this.optionsUiLayer?.dispose();
					})
		];
	}
}
