export class SiIdentifier {
	constructor(public category: string , public id: string|null) {
		
	}
	
	equals(obj: any): boolean {
		return obj instanceof SiIdentifier && this.category == (<SiIdentifier>obj).category
				&& this.id == (<SiIdentifier>obj).id
	}
}

export class SiQualifier extends SiIdentifier {
	constructor(category: string, id: string|null, public buildupId, public typeName: string, 
			public iconClass: string, public idName: string|null) {
		super(category, id)
	}
	
	equals(obj: any): boolean {
		return obj instanceof SiQualifier && super.equals(obj);
	}
}