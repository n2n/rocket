import { Component, OnInit } from '@angular/core';
import { EmbeddedEntryInModel } from "src/app/ui/content/field/embedded-entry-in-model";

@Component({
  selector: 'rocket-embedded-entry-in-field',
  templateUrl: './embedded-entry-in-field.component.html',
  styleUrls: ['./embedded-entry-in-field.component.css']
})
export class EmbeddedEntryInFieldComponent implements OnInit {

	model: EmbeddedEntryInModel;

	constructor() { }

	ngOnInit() {
	}

}
