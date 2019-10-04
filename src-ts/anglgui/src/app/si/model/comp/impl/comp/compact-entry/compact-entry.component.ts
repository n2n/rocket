import { Component, OnInit } from '@angular/core';
import { CompactEntrySiComp } from 'src/app/si/model/entity/impl/basic/compact-entry-si-comp';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';

@Component({
	selector: 'rocket-compact-entry',
	templateUrl: './compact-entry.component.html',
	styleUrls: ['./compact-entry.component.css']
})
export class CompactEntryComponent implements OnInit {
	siStructure: UiStructure;
	siContent: CompactEntrySiComp;

	constructor() { }

	ngOnInit() {
	}
}
