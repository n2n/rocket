import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CompactEntryComponent } from './compact-entry.component';

describe('CompactEntryComponent', () => {
  let component: CompactEntryComponent;
  let fixture: ComponentFixture<CompactEntryComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CompactEntryComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CompactEntryComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
