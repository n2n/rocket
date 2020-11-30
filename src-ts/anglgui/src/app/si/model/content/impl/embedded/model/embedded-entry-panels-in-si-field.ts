import { SiPanel } from './si-panel';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { EmbeddedEntryPanelsInComponent } from '../comp/embedded-entry-panels-in/embedded-entry-panels-in.component';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiService } from 'src/app/si/manage/si.service';
import { PanelDef } from '../comp/embedded-entry-panels-in-model';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { EmbeddedEntryObtainer } from './embedded-entry-obtainer';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { EmbeddedEntriesInUiStructureModel } from './embedded-entries-in-ui-structure-model';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { UiZoneError } from 'src/app/ui/structure/model/ui-zone-error';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiModStateService } from 'src/app/si/model/mod/model/si-mod-state.service';
import { GenericEmbeddedEntryManager } from './generic/generic-embedded-entry-manager';

class GenericSiPanelValueCollection {
	public map = new Map<string, SiGenericValue>();
}

export class EmbeddedEntryPanelsInSiField extends SiFieldAdapter  {

	constructor(public siService: SiService, public siModState: SiModStateService, public frame: SiFrame,
			public translationService: TranslationService, public panels: SiPanel[]) {
		super();
	}

	getPanels(): SiPanel[] {
		return this.panels;
	}

	getApiUrl(): string {
		return this.frame.apiUrl;
	}

	hasInput(): boolean {
		return true;
	}

	readInput(): object {
		throw new Error('not yet implemented');
	}

	createUiStructureModel(): UiStructureModel {
		const panelAssemblies = this.panels.map((panel) => {
			const obtainer = new EmbeddedEntryObtainer(this.siService, this.siModState,
					this.frame, panel.reduced, panel.allowedTypeIds);

			return {
				panel,
				structureModel: new EmbeddedEntriesInUiStructureModel(obtainer, this.frame, panel,
						panel, this.translationService)
			};
		});

		return new EmbeddedEntryPanelsInUiStructureModel(panelAssemblies);
	}

	// createUiContent(uiStructure: UiStructure): UiContent {
	// 	return new TypeUiContent(EmbeddedEntryPanelsInComponent, (ref) => {
	// 		ref.instance.model = this;
	// 		ref.instance.uiStructure = uiStructure;
	// 	});
	// }


	private createGenericManager(panel: SiPanel): GenericEmbeddedEntryManager {
		return new GenericEmbeddedEntryManager(panel.values, this.siService, this.siModState, this.frame, this, panel.reduced,
				panel.allowedTypeIds);
	}

	copyValue(): SiGenericValue {
		const col = new GenericSiPanelValueCollection();

		for (const panel of this.panels) {
			col.map.set(panel.name, this.createGenericManager(panel).copyValue());
		}

		return new SiGenericValue(col);
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		const col = genericValue.readInstance(GenericSiPanelValueCollection)
		const promises = new Array<Promise<void>>();

		for (const panel of this.panels) {
			if (!col.map.has(panel.name)) {
				continue;
			}

			promises.push(this.createGenericManager(panel).pasteValue(col.map.get(panel.name)));
		}

		return Promise.all(promises).then(() => { return; });

	}

	createResetPoint(): SiGenericValue {
		const col = new GenericSiPanelValueCollection();

		for (const panel of this.panels) {
			col.map.set(panel.name, this.createGenericManager(panel).createResetPoint());
		}

		return new SiGenericValue(col);
	}

	resetToPoint(genericValue: SiGenericValue): void {
		const col = genericValue.readInstance(GenericSiPanelValueCollection);

		for (const panel of this.panels) {
			if (!col.map.has(panel.name)) {
				throw new GenericMissmatchError('ResetPoint contains no data for panel: ' + panel.name);
			}

			this.createGenericManager(panel).resetToPoint(col.map.get(panel.name));
		}
	}
}

class EmbeddedEntryPanelsInUiStructureModel extends UiStructureModelAdapter {

	constructor(private panelAssemblies: Array<{panel: SiPanel, structureModel: EmbeddedEntriesInUiStructureModel}>) {
		super();
	}

	bind(uiStructure: UiStructure) {
		const panelDefs = new Array<PanelDef>();
		for (const panelAssembly of this.panelAssemblies) {
			panelDefs.push({
				siPanel: panelAssembly.panel,
				uiStructure: uiStructure.createChild(UiStructureType.SIMPLE_GROUP,
						panelAssembly.panel.label, panelAssembly.structureModel)
			});
		}

		this.uiContent = new TypeUiContent(EmbeddedEntryPanelsInComponent, (ref) => {
			ref.instance.model = {
				getPanelDefs: () => panelDefs
			};
		});
	}


	getZoneErrors(): UiZoneError[] {
		const uiZoneErrors = new Array<UiZoneError>();
		for (const panelAssembly of this.panelAssemblies) {
			uiZoneErrors.push(...panelAssembly.structureModel.getZoneErrors());
		}
		return uiZoneErrors;
	}
}



