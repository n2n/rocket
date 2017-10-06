
jQuery(document).ready(function($) {
	
	$(".rocket-impl-cke-classic").each((i, elem) => {
		ClassicEditor
			    .create(elem, {
			    	toolbar: $(elem).data("rocket-impl-toolbar")
			    })
			    .then( editor => {
			        console.log( editor );
			    })
			    .catch( error => {
			        console.error( error );
			    });
	});	
});