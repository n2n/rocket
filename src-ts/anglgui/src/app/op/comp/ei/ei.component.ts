import { Component, OnInit, ViewChild, ComponentFactoryResolver } from '@angular/core';
import { ZoneDirective } from "src/app/op/comp/ei/zone.directive";
import { Route, ActivatedRoute, Router } from "@angular/router";
import { PlatformLocation } from "@angular/common";
import { ZoneComponent } from "src/app/ui/structure/comp/zone.component";
import { SiService } from "src/app/op/model/si.service";
import { SiContainer } from "src/app/si/structure/si-container";

@Component({
  selector: 'rocket-ei',
  templateUrl: './ei.component.html',
  styleUrls: ['./ei.component.css']
})
export class EiComponent implements OnInit {

    private siContainer: SiContainer;
    
    @ViewChild(ZoneDirective) zoneDirective: ZoneDirective;
    
    
    constructor(private componentFactoryResolver: ComponentFactoryResolver, private route: ActivatedRoute, 
            private siService: SiService/*private router: Router, platformLocation: PlatformLocation*/) {
        this.siContainer = new SiContainer();
//        alert(platformLocation.getBaseHrefFromDOM() + ' ' + route.snapshot.url.join('/'));
    }

    ngOnInit() {
        this.siService.lookupSiZone(this.route.snapshot.url.join('/')).subscribe((siZone) => {
            console.log(siZone);
        });
        
//        const componentFactory = this.componentFactoryResolver.resolveComponentFactory(ListZoneComponent);
//        
//        const componentRef = this.zoneDirective.viewContainerRef.createComponent(componentFactory);
//        
//        (<ZoneComponent> componentRef.instance).data = {};
    }
}
