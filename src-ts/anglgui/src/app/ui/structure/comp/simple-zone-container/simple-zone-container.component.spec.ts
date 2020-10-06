import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SimpleZoneContainerComponent } from './simple-zone-container.component';

describe('SimpleZoneContainerComponent', () => {
  let component: SimpleZoneContainerComponent;
  let fixture: ComponentFixture<SimpleZoneContainerComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SimpleZoneContainerComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SimpleZoneContainerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
