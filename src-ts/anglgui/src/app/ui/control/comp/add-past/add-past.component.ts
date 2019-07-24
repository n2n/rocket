import { Component, OnInit, Input } from '@angular/core';
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiQualifier } from "src/app/si/model/content/si-qualifier";
import { ClipboardService } from "src/app/si/model/content/clipboard.service";
import { SiService } from "src/app/si/model/si.service";
import { SiGetRequest } from "src/app/si/model/api/si-get-request";
import { SiGetInstruction } from "src/app/si/model/api/si-get-instruction";

@Component({
  selector: 'rocket-ui-add-past',
  templateUrl: './add-past.component.html',
  styleUrls: ['./add-past.component.css']
})
export class AddPastComponent implements OnInit {

	@Input()
	apiUrl: string;
	
	@Input()
	pasteCategory: string|null = null;
	
	@Input()
	allowedTypeNames: string[]|null = null;
	
	@Input()
	summaryRequired: boolean;
	
	constructor(private clipboardService: ClipboardService, private siService: SiService) {
	}

	ngOnInit() {
	}
	
	add() {
		
		new BulkySiZone();
		
		this.siService.apiGet(this.apiUrl, 
				new SiGetRequest(SiGetInstruction.newEntry(true, false), SiGetInstruction.newEntry(true, true)), 
				zone, zoneContent);
	}
	
	get visiablePastables(): SiQualifier[] {
		return this.clipboardService.getByCategory(this.pasteCategory)
	}
	
	isAllowed(siQualifier: SiQualifier): boolean {
		if (siQualifier.category != this.pasteCategory) {
			return false;
		}
		
		return !this.allowedTypeNames || this.allowedTypeNames.indexOf(siQualifier.typeName);
	}
	

}

interface AddPastOptainer {
	optainSiEmbeddedEntry: (siS) => any;
}
