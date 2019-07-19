import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BulkyEntryComponent } from './bulky-entry.component';

describe('BulkyEntryComponent', () => {
  let component: BulkyEntryComponent;
  let fixture: ComponentFixture<BulkyEntryComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BulkyEntryComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BulkyEntryComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
