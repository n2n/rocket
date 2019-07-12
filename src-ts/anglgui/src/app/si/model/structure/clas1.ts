import { Clas2 } from "src/app/si/model/structure/clas2";


export class Clas1 {
	public clas2: Clas2
	
	constructor() {
	}

	static create(clas2: Clas2) {
		return new Clas1();
	}

} 