import { Component, OnInit } from '@angular/core';
import { SiLayer } from "src/app/si/structure/si-layer";
import { Input } from "@angular/core";

@Component({
  selector: 'rocket-ui-layer',
  templateUrl: './layer.component.html',
  styleUrls: ['./layer.component.css']
})
export class LayerComponent implements OnInit {
    @Input()
    siLayer: SiLayer;
    
    constructor() { }

    ngOnInit() {
    }

}
