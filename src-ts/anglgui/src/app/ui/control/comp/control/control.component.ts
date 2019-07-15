import { Component, OnInit, Input } from '@angular/core';
import { SiControl } from "src/app/si/model/control/si-control";
import { SiButton } from "src/app/si/model/control/si-button";
import { Router } from "@angular/router";
import { SiCommanderService } from "src/app/si/model/si-commander.service";

@Component({
  selector: 'rocket-ui-control',
  templateUrl: './control.component.html',
  styleUrls: ['./control.component.css']
})
export class ControlComponent implements OnInit {

    @Input() siControl: SiControl;
    
    constructor(private siCommanderService: SiCommanderService) {
    	
    }
    
	ngOnInit() {
	}
	
	get siButton(): SiButton {
		return this.siControl.getButton();
	}
	
	get loading() {
		return this.siControl.isLoading();
	}
	
	exec() {
		this.siControl.exec(this.siCommanderService);
	}
}
