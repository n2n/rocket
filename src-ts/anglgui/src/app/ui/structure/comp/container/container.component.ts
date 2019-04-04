import { Component, OnInit } from '@angular/core';
import { SiContainer } from "src/app/si/structure/si-container";
import { Input } from "@angular/core";

@Component({
  selector: 'rocket-ui-container',
  templateUrl: './container.component.html',
  styleUrls: ['./container.component.css']
})
export class ContainerComponent implements OnInit {
    @Input()
    siContainer: SiContainer;
    
    constructor() { }

    ngOnInit() {
    }

}
