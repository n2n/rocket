import { Component, OnInit, Input } from '@angular/core';
import { UiStructure } from '../../model/ui-structure';
import { UiContent } from '../../model/ui-content';

@Component({
	selector: 'rocket-ui-structure-branch',
	templateUrl: './structure-branch.component.html',
	styleUrls: ['./structure-branch.component.css']
})
export class StructureBranchComponent implements OnInit {
	@Input()
	uiStructure: UiStructure;
	@Input()
	uiContent: UiContent|null = null;
	// @Input()
	// uiStructures: SiStructure[] = [];

	constructor() { }

	ngOnInit() {
	}
}
