import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ChPwComponent } from './ch-pw.component';

describe('ChPwComponent', () => {
  let component: ChPwComponent;
  let fixture: ComponentFixture<ChPwComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ChPwComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ChPwComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
