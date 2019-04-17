import { Component, OnInit, Input } from '@angular/core';
import { ListSiZoneContent } from "src/app/si/model/structure/impl/list-si-zone-content";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiField } from "src/app/si/model/content/si-field";

@Component({
  selector: 'rocket-ui-list-zone-content',
  templateUrl: './list-zone-content.component.html',
  styleUrls: ['./list-zone-content.component.css']
})
export class ListZoneContentComponent implements OnInit {

	listSiZone: ListSiZoneContent;
	
	constructor() { }

	ngOnInit() {
	}
}
