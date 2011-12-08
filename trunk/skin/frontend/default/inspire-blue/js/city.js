 function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}


function toggleCityChanger()
{
    var obj = document.getElementById("city-switcher");
    if(obj.style.display == 'none') {
        obj.style.display='block';
        eraseCookie('close_city_changer');
    }else {
        obj.style.display='none';
        createCookie('close_city_changer',1,0);
    }
}