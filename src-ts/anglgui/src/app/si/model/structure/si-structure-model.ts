
import { ComponentFactoryResolver, ViewContainerRef, ComponentRef } from "@angular/core";
import { SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiCommanderService } from "src/app/si/model/si-commander.service";
import { SiZoneError } from "src/app/si/model/structure/si-zone-error";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiContent } from "src/app/si/model/structure/si-content";

export interface SiStructureModel {

	getContent(): SiContent|null;

	getChildren(): SiStructure[];

	getControls(): SiControl[];

	getZoneErrors(): SiZoneError[];
}


