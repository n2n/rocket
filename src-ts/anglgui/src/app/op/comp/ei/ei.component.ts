import { Component, OnInit, ViewChild, ComponentFactoryResolver } from '@angular/core';
import { Route, ActivatedRoute, Router, UrlSegment } from "@angular/router";
import { PlatformLocation } from "@angular/common";
import { SiService } from "src/app/op/model/si.service";
import { SiContainer } from "src/app/si/model/structure/si-container";
import { SiZone } from "src/app/si/model/structure/si-zone";

@Component({
  selector: 'rocket-ei',
  templateUrl: './ei.component.html',
  styleUrls: ['./ei.component.css']
})
export class EiComponent implements OnInit {

    private siContainer: SiContainer;
    
    
    
    constructor(private componentFactoryResolver: ComponentFactoryResolver, private route: ActivatedRoute, 
            private siService: SiService/*private router: Router, platformLocation: PlatformLocation*/) {
        this.siContainer = new SiContainer();
//        alert(platformLocation.getBaseHrefFromDOM() + ' ' + route.snapshot.url.join('/'));
    }

    ngOnInit() {
    	this.route.url.subscribe((url: UrlSegment[]) => {
    		const siZone = new SiZone();
    		
    		console.log(url);
    		
    		this.siContainer.mainSiLayer.pushSiZone(siZone);
    		
    		this.siService.lookupSiZoneContent(url.join('/'))
		    		.subscribe((siZoneContent) => {
		    			siZone.content = siZoneContent;
		            });
    	});
    }
}
