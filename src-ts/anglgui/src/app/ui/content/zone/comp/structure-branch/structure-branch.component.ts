import { Component, OnInit, Input } from '@angular/core';
import { UiContent } from 'src/app/si/model/structure/ui-content';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';

@Component({
	selector: 'rocket-ui-structure-branch',
	templateUrl: './structure-branch.component.html',
	styleUrls: ['./structure-branch.component.css']
})
export class StructureBranchComponent implements OnInit {
	@Input()
	siStructure: UiStructure;
	@Input()
	siContent: UiContent|null = null;
	// @Input()
	// siStructures: SiStructure[] = [];

	constructor() { }

	ngOnInit() {
	}
}
