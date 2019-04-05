import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListZoneContentComponent } from './list-zone-content.component';

describe('ListZoneContentComponent', () => {
  let component: ListZoneContentComponent;
  let fixture: ComponentFixture<ListZoneContentComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListZoneContentComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListZoneContentComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
