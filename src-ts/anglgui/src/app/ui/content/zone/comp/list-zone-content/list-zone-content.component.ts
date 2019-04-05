import { Component, OnInit, Input } from '@angular/core';
import { ListSiZone } from "src/app/si/model/structure/impl/list-si-zone";

@Component({
  selector: 'rocket-list-zone-content',
  templateUrl: './list-zone-content.component.html',
  styleUrls: ['./list-zone-content.component.css']
})
export class ListZoneContentComponent implements OnInit {

	listSiZone: ListSiZone;
	
	constructor() { }

	ngOnInit() {
	}
}
