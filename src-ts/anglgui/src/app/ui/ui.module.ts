import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { UtilModule } from 'src/app/util/util.module';
import { RouterModule } from '@angular/router';
import { PlainContentComponent } from './structure/comp/plain-content/plain-content.component';
import { StructureContentDirective } from './structure/comp/structure/structure-content.directive';
import { StructureBranchComponent } from './structure/comp/structure-branch/structure-branch.component';
import { ContainerComponent } from './structure/comp/container/container.component';
import { ZoneComponent } from './structure/comp/zone/zone.component';
import { LayerComponent } from './structure/comp/layer/layer.component';
import { StructureComponent } from './structure/comp/structure/structure.component';
import { MessageComponent } from './util/comp/message/message.component';
import { BreadcrumbsComponent } from './structure/comp/inc/breadcrumbs/breadcrumbs.component';

@NgModule({
	declarations: [
		LayerComponent, ContainerComponent, ZoneComponent, StructureComponent, StructureContentDirective,
		StructureBranchComponent, PlainContentComponent, MessageComponent, BreadcrumbsComponent
	],
	imports: [
		CommonModule,
		UtilModule,
		RouterModule
	],
	exports: [
		ContainerComponent,
		StructureComponent,
		StructureContentDirective,
		StructureBranchComponent,
		PlainContentComponent,
		MessageComponent
	],
	entryComponents: [ StructureBranchComponent, PlainContentComponent ]
})
export class UiModule { }
