import { Component, OnInit, Input } from '@angular/core';
import { SiControl } from "src/app/si/model/control/si-control";
import { SiButton } from "src/app/si/model/control/si-button";
import { Router } from "@angular/router";
import { SiService } from "src/app/op/model/si.service";

@Component({
  selector: 'rocket-ui-control',
  templateUrl: './control.component.html',
  styleUrls: ['./control.component.css']
})
export class ControlComponent implements OnInit {

    @Input() siControl: SiControl;
    
    constructor(private siService: SiService) {
    	
    }
    
	ngOnInit() {
	}
	
	get siButton(): SiButton {
		return this.siControl.getButton();
	}
	
	exec() {
		this.siControl.exec(this.siService);
	}
}
