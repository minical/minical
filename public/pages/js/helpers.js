
//Function getBaseURL
//returns baseURL
var getBaseURL = function () {
	
	pathArray = window.location.href.split( '/' );
	protocol = pathArray[0];
	host = pathArray[2];
	url = protocol + '//' + host + "/";
	if (host == "localhost") 
	{
		url = 'http://localhost/minical/public/pages/'; // for local development
	}
	
	return url;
}
