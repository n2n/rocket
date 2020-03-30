import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EmbeddedEntryComponent } from './embedded-entry.component';

describe('EmbeddedEntryComponent', () => {
  let component: EmbeddedEntryComponent;
  let fixture: ComponentFixture<EmbeddedEntryComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EmbeddedEntryComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EmbeddedEntryComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
