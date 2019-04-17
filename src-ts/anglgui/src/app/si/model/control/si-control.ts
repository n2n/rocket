import { ViewContainerRef, ComponentFactoryResolver } from "@angular/core";
import { SiButton } from "src/app/si/model/control/si-button";
import { Router } from "@angular/router";
import { PlatformLocation } from "@angular/common";
import { SiService } from "src/app/op/model/si.service";

export interface SiControl {
	
	getButton(): SiButton;
	
	exec(siService: SiService);
}