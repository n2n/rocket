import { Component, OnInit, OnDestroy } from '@angular/core';
import { SiEntry } from 'src/app/si/model/entity/si-entry';
import { SiControl } from 'src/app/si/model/control/si-control';
import { BulkyEntrySiComp } from 'src/app/si/model/entity/impl/basic/bulky-entry-si-comp';
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiFieldStructureDeclaration } from "src/app/si/model/entity/si-field-structure-declaration";
import { SimpleSiStructureModel } from "src/app/si/model/structure/impl/simple-si-structure-model";
import { TypeSiContent } from "src/app/si/model/structure/impl/type-si-content";
import { StructureBranchComponent } from "src/app/ui/content/zone/comp/structure-branch/structure-branch.component";

@Component({
  selector: 'rocket-bulky-entry',
  templateUrl: './bulky-entry.component.html'
})
export class BulkyEntryComponent implements OnInit, OnDestroy {

	public model: BulkyEntrySiComp;
    public siStructure: SiStructure;

    public fieldSiStructures: SiStructure[];

	constructor() { }

	ngOnInit() {
	    this.fieldSiStructures = this.createStructures(this.siStructure, this.model.getFieldStructureDeclarations());
	}
	
	ngOnDestroy() {
	    let siStructure: SiStructure|null = null;
	    while (siStructure = this.fieldSiStructures.pop()) {
	        siStructure.dispose();
	    }
	}

	get siEntry(): SiEntry {
		return this.model.entry;
	}
	
    private createStructures(parent: SiStructure, fieldStructureDeclarations: SiFieldStructureDeclaration[]): SiStructure[] {
        const structures: SiStructure[] = [];
        for (const fsd of fieldStructureDeclarations) {
            structures.push(this.dingsel(parent, fsd));
        }
        return structures;
    }

//  getChildren(): SiStructure[] {
//      if (this.children) {
//          return this.children;
//      }
//
//      this.children = [];
//      const declarations = this.getFieldStructureDeclarations();
//      for (const child of declarations) {
//          this.children.push(this.dingsel(this.entry, child));
//      }
//      return this.children;
//  }

    private dingsel(parent: SiStructure, fsd: SiFieldStructureDeclaration): SiStructure {
        const structure = new SiStructure(parent);
        structure.label = fsd.fieldDeclaration.label;
        structure.type = fsd.type;

        const field = this.siEntry.selectedTypeBuildup.getFieldById(fsd.fieldDeclaration.fieldId);
        const model = new SimpleSiStructureModel(
                new TypeSiContent(StructureBranchComponent, (ref, structure) => {
                    ref.instance.siStructure = structure;
                    ref.instance.siContent = field ? field.getContent() : null;
                    ref.instance.siStructures = this.createStructures(structure, fsd.children)
                }));
        structure.model = model;
        
        return structure;
    }

}
