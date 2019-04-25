import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { SiButton } from "src/app/si/model/control/si-button";
import { Router } from "@angular/router";
import { PlatformLocation } from "@angular/common";
import { SiCommanderService } from "src/app/si/model/si-commander.service";

export interface SiControl {
	
	getButton(): SiButton;
	
	exec(siCommanderService: SiCommanderService);
}