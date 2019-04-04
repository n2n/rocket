import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from "rxjs";
import { map } from "rxjs/operators";
import { Extractor } from "src/app/util/mapping/extractor";
import { SiZone } from "src/app/si/structure/si-zone";
import { ListSiZone } from "src/app/si/structure/impl/list-si-zone";

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

