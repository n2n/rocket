import { Component, OnInit } from '@angular/core';
import { CompactEntrySiContent } from "src/app/si/model/structure/impl/compact-entry-si-content";

@Component({
  selector: 'rocket-compact-entry',
  templateUrl: './compact-entry.component.html',
  styleUrls: ['./compact-entry.component.css']
})
export class CompactEntryComponent implements OnInit {

	siContent: CompactEntrySiContent;

	constructor() { }

	ngOnInit() {
	}

}
