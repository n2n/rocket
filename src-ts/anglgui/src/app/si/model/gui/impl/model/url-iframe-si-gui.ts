import {SiGui} from "../../si-gui";

export class IframeSiGui implements SiGui {
	url: string|null;
	srcDoc: string|null;

  createUiStructureModel(): UiStructureModel {
    return undefined;
  }


}
