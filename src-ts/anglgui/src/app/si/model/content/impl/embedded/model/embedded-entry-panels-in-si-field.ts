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

export class EmbeddedEntryPanelsInSiField extends SiFieldAdapter  {

	constructor(public siService: SiService, public typeCatgory: string, public apiUrl: string,
			public translationService: TranslationService, public panels: SiPanel[]) {
		super();
	}

	getPanels(): SiPanel[] {
		return this.panels;
	}

	getApiUrl(): string {
		return this.apiUrl;
	}

	hasInput(): boolean {
		return true;
	}

	readInput(): object {
		throw new Error('Not yet implemented.');
	}

	createUiStructureModel(): UiStructureModel {
		const obtainer = new EmbeddedEntryObtainer(this.siService, this.apiUrl, true);

		const panelAssemblies = this.panels.map((panel) => {
			return {
				panel,
				structureModel: new EmbeddedEntriesInUiStructureModel(obtainer, this.typeCatgory, panel,
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

	copyValue(): SiGenericValue {
		throw new Error('Not yet implemented.');
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		throw new Error('Not yet implemented.');
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



