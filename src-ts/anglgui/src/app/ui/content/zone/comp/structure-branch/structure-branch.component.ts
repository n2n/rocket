import { Component, OnInit, Input } from '@angular/core';
import { SiContent } from "src/app/si/model/structure/si-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";

@Component({
  selector: 'rocket-ui-structure-branch',
  templateUrl: './structure-branch.component.html',
  styleUrls: ['./structure-branch.component.css']
})
export class StructureBranchComponent implements OnInit {
    @Input()
    siStructure: SiStructure
    @Input()
    siContent: SiContent|null = null;
    @Input()
    siStructures: SiStructure[] = [];
  
    constructor() { }

    ngOnInit() {
    }

}
