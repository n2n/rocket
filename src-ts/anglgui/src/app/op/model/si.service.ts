import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from "rxjs";
import { map } from "rxjs/operators";
import { Extractor, ObjectMissmatchError } from "src/app/util/mapping/extractor";
import { SiZone } from "src/app/si/structure/si-zone";
import { ListSiZone } from "src/app/si/structure/impl/list-si-zone";
import { DlSiZone } from "src/app/si/structure/impl/dl-si-zone";

@Injectable({
  providedIn: 'root'
})
export class SiService {

  constructor(private httpClient: HttpClient) { 
  }
  
  lookupSiZone(url: string): Observable<SiZone> {
      return this.httpClient.get<any>(url)
            .pipe(map((data: any) => {
                return this.createSiZone(data);
            }));
  }
  
  private createSiZone(data: any): SiZone {
      const extr = new Extractor(data);
      
      alert(Object.keys(ZoneType));
      return new ListSiZone();
  }
}

export enum ZoneType {
    LIST = 'list',
    DL = 'dl'
} 


class SiZoneFactory {
	
	static create(data: any): SiZone {
		const extr = new Extractor(data);
		
		switch (extr.reqString('type')) {
			case ZoneType.LIST:
				return new ListSiZone(/*
						SiZoneFactory.createList(extr.reqArray('itemDeclarations')),
						SiZoneFactory.createEntries(extr.reqArray('entries'), true)*/);
			case ZoneType.DL:
				return new DlSiZone();
			default:
				throw new ObjectMissmatchError('Invalid si zone type: ' + data.type);
		}
	}
	
	private static createCompact(data: Array<any>) {

	}
	
	private static createBulky(data: Array<any>) {

	}
	
	private static createEntries(data: Array<any>) {

	}
}
