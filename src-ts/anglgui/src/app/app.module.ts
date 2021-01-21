import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { OpModule } from 'src/app/op/op.module';
import {BrowserAnimationsModule, NoopAnimationsModule} from "@angular/platform-browser/animations";
import { UtilModule } from './util/util.module';

@NgModule({
	declarations: [
	AppComponent
	],
	imports: [
  BrowserAnimationsModule,
  NoopAnimationsModule,
	BrowserModule,
	AppRoutingModule,
	OpModule,
	UtilModule
	],
	providers: [],
	bootstrap: [AppComponent]
})
export class AppModule { }
