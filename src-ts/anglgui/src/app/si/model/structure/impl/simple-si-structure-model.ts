import { SiStructureModel } from '../si-structure-model';
import { SiControl } from '../../control/si-control';
import { SiStructure } from '../si-structure';
import { SiContent } from '../si-content';
import { SiZoneError } from '../si-zone-error';
import { ComponentFactoryResolver, ViewContainerRef, ComponentRef } from '@angular/core';
import { SiCommanderService } from '../../si-commander.service';

export class SimpleSiStructureModel implements SiStructureModel {
	public controls: SiControl[] = [];
	public children: SiStructure[] = [];
	public zoneErrors: SiZoneError[] = [];

	constructor(public content: SiContent|null = null) {
	}

	getContent(): SiContent|null {
		return this.content;
	}

	getChildren(): SiStructure[] {
		return this.children;
	}

	getControls(): SiControl[] {
		return this.controls;
	}

	getZoneErrors(): SiZoneError[] {
		return this.zoneErrors;
	}
}
