import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { DragDropModule	} from '@angular/cdk/drag-drop';
import { UtilModule } from 'src/app/util/util.module';
import { RouterModule } from '@angular/router';
import { StructureBranchComponent } from './content/zone/comp/structure-branch/structure-branch.component';
import { PlainContentComponent } from './structure/comp/plain-content/plain-content.component';

@NgModule({
	declarations: [
		LayerComponent, ContainerComponent, ZoneComponent, StructureContentDirective, StructureBranchComponent, PlainContentComponent 
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
		ControlComponent
	],
	entryComponents: [ StructureBranchComponent ]
})
export class UiModule { }
