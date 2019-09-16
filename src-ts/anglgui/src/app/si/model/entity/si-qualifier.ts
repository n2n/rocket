import { SiType } from "src/app/si/model/entity/si-type";

export class SiIdentifier {
	constructor(public category: string , public id: string|null) {
		
	}
	
	equals(obj: any): boolean {
		return obj instanceof SiIdentifier && this.category == (<SiIdentifier>obj).category
				&& this.id == (<SiIdentifier>obj).id
	}
}

export class SiQualifier extends SiIdentifier {
	constructor(category: string, id: string|null, public type: SiType, public idName: string|null) {
		super(category, id)
	}
	
	equals(obj: any): boolean {
		return obj instanceof SiQualifier && super.equals(obj);
	}
}