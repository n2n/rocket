import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { DragDropModule	} from '@angular/cdk/drag-drop';
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

@NgModule({
	declarations: [
		LayerComponent, ContainerComponent, ZoneComponent, StructureComponent, StructureContentDirective,
		StructureBranchComponent, PlainContentComponent, MessageComponent
	],
	imports: [
		CommonModule,
		FormsModule,
		UtilModule,
		RouterModule,
		DragDropModule
	],
	exports: [
		ContainerComponent,
		LayerComponent,
		StructureComponent
	],
	entryComponents: [ StructureBranchComponent ]
})
export class UiModule { }
