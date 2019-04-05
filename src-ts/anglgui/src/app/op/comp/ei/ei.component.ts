import { Component, OnInit, ViewChild, ComponentFactoryResolver } from '@angular/core';
import { Route, ActivatedRoute, Router } from "@angular/router";
import { PlatformLocation } from "@angular/common";
import { SiService } from "src/app/op/model/si.service";
import { SiContainer } from "src/app/si/model/structure/si-container";

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
    	this.siService.lookupSiZone(this.route.snapshot.url.join('/')).subscribe((siZone) => {
    		this.siContainer.mainSiLayer.pushSiZone(siZone);
        });
        
    }
}
