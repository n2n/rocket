import { Component, OnInit, Input } from '@angular/core';
import { ZoneComponent } from "src/app/ui/zone/comp/zone.component";

@Component({
  selector: 'rocket-ui-list-zone',
  templateUrl: './list-zone.component.html',
  styleUrls: ['./list-zone.component.css']
})
export class ListZoneComponent implements ZoneComponent, OnInit {
    @Input() data: any;
    
    constructor() { }

    ngOnInit() {
    }
}
