import { Component, OnInit } from '@angular/core';
import { CompactEntrySiComp } from 'src/app/si/model/entity/impl/basic/compact-entry-si-comp';
import { SiStructure } from 'src/app/si/model/structure/si-structure';

@Component({
  selector: 'rocket-compact-entry',
  templateUrl: './compact-entry.component.html',
  styleUrls: ['./compact-entry.component.css']
})
export class CompactEntryComponent implements OnInit {
	siStructure: SiStructure;
	siContent: CompactEntrySiComp;

	constructor() { }

	ngOnInit() {
	}
}
