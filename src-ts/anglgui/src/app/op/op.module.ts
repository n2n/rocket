import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EiComponent } from './comp/ei/ei.component';
import { OpRoutingModule } from 'src/app/op/op-routing.module';
import { UiModule } from 'src/app/ui/ui.module';
import { FallbackComponent } from 'src/app/op/comp/common/fallback/fallback.component';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule } from '@angular/common/http';
import { SiModule } from '../si/si.module';
import { UsersComponentComponent } from './user/comp/users-component/users-component.component';

@NgModule({
	declarations: [EiComponent, FallbackComponent, UsersComponentComponent ],
	imports: [
	CommonModule,
	OpRoutingModule,
	UiModule,
	HttpClientModule,
	SiModule
	]
})
export class OpModule { }
