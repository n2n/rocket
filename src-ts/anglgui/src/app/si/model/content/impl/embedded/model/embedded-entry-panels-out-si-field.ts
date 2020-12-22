import { SiPanel } from './si-panel';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { UiZoneError } from 'src/app/ui/structure/model/ui-zone-error';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiModStateService } from 'src/app/si/model/mod/model/si-mod-state.service';
import { GenericEmbeddedEntryManager } from './generic/generic-embedded-entry-manager';
import { EmbeddedEntriesOutUiStructureModel } from './embedded-entries-out-ui-structure-model';
import { PanelDef } from '../comp/embedded-entry-panels-model';
import { EmbeddedEntryPanelsComponent } from '../comp/embedded-entry-panels/embedded-entry-panels.component';

class GenericSiPanelValueCollection {
	public map = new Map<string, SiGenericValue>();
}

export class EmbeddedEntryPanelsOutSiField extends SiFieldAdapter  {

	constructor(public siService: SiService, public siModState: SiModStateService, public frame: SiFrame,
			public translationService: TranslationService, public panels: SiPanel[]) {
		super();
	}

	getPanels(): SiPanel[] {
		return this.panels;
	}

	hasInput(): boolean {
		return false;
	}

	readInput(): object {
		throw new Error('not input');
	}

	createUiStructureModel(): UiStructureModel {
		const panelAssemblies = this.panels.map((panel) => {
			return {
				panel,
				structureModel: new EmbeddedEntriesOutUiStructureModel(this.frame, panel, panel, this.translationService)
			};
		});

		return new EmbeddedEntryPanelsOutUiStructureModel(panelAssemblies);
	}

	// createUiContent(uiStructure: UiStructure): UiContent {
	// 	return new TypeUiContent(EmbeddedEntryPanelsComponent, (ref) => {
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

class EmbeddedEntryPanelsOutUiStructureModel extends UiStructureModelAdapter {

	constructor(private panelAssemblies: Array<{panel: SiPanel, structureModel: EmbeddedEntriesOutUiStructureModel}>) {
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

		this.uiContent = new TypeUiContent(EmbeddedEntryPanelsComponent, (ref) => {
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


