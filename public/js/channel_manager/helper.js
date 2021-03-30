//Global Helper Functions

//Function getBaseURL
//returns baseURL
var getBaseURL = function () {
	
	pathArray = window.location.href.split( '/' );
	protocol = pathArray[0];
	host = pathArray[2];
	url = base_url;//protocol + '//' + host + "/";
	
	return url+'/';
}
