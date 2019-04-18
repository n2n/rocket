import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DlZoneContentComponent } from './dl-zone-content.component';

describe('DlZoneContentComponent', () => {
  let component: DlZoneContentComponent;
  let fixture: ComponentFixture<DlZoneContentComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DlZoneContentComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DlZoneContentComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
