import { Component, OnInit } from '@angular/core';
import { CompactEntrySiComp } from 'src/app/si/model/content/impl/basic/compact-entry-si-comp';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';

@Component({
	selector: 'rocket-compact-entry',
	templateUrl: './compact-entry.component.html',
	styleUrls: ['./compact-entry.component.css']
})
export class CompactEntryComponent implements OnInit {
	uiStructure: UiStructure;
	uiContent: CompactEntrySiComp;

	constructor() { }

	ngOnInit() {
	}
}
