import { Clas1 } from "src/app/si/model/structure/clas1";

export class Clas2 {
	public clas1: Clas1;

	constructor() {
		
	}

	create(): Clas1 {
		return Clas1.create(this);
	}
} 