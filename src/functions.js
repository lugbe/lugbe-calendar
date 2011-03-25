<!--
/* javascript functions for including in the lugbe.ch pages */


function checkLinux(platform) {
	var lxReg=/linux/i;
	if(lxReg.test(platform)) return true;
	else return false;
}

function makePopupWindow(aName,anURL) {
	hasSeen=true;
    	newPopupWindow = window.open(anURL,aName,"toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=0,resizable=0,dependent=0,height=300,width=300,left=200,screenX=200,top=100,screenY=100");
    	newPopupWindow.name=aName;
    	newPopupWindow.focus(); 
//	newPopupWindow.setTimeout("window.close()",5000);
}

function reorder(a,b,c) {

	var res=b;
	res+=a;
	res+='@';
	res+=c;

	return res;
}

function show_add(a,b,c) {
	document.write(reorder(a,b,c));
}

function link_add(a,b,c) {
	var location="mai";
	location+="lto:";
	location+=reorder(a,b,c);
	parent.location=location;
}

function showRegistrations(IDEvent) {
	var winName="Anmeldungen f&uuml;r Event Nr. " + IDEvent;
	var winURL="/action/events/reg.php?IDEvent=" + IDEvent;
	makePopupWindow(winName, winURL);
	return true;	
}
// -->
