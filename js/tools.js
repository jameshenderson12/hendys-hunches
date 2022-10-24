// MY UTILS

window.onerror = function(error, url, line) {
	var txt = "ERROR ON PAGE :: " + error
				+ "\nURL :: " + url
				+ "\nLINE NUMBER :: " + line;
	alert(txt);				
}

function getID(someID) {
	return document.getElementById(someID);
}

function setText(someID, txt, tag) {
	var oTag = "<" + tag + ">";
	var eTag = "</" + tag + ">";
	getID(someID).innerHTML += oTag + txt + eTag;
}

function checkData(myID, myTag) {
	if (localStorage.length > 0) {
		for(var i = 0; i < localStorage.length; i++) {
			setText(myID, localStorage.getItem(localStorage.key(i)), myTag);
		}
	}
}

function Grid(context, width, height, spacing, colour) {
	this.context = context;
	this.context.save();
	this.width = width;
	this.height = height;
	this.spacing = spacing;
	this.context.strokeStyle = colour;
	this.numrows = height/spacing;
	this.numcols = width/spacing;
}

Grid.prototype.build = function() {
	this.context.beginPath();
	for(var i = 0; i < this.numrows; i++) {
		this.context.moveTo(0, this.spacing * i);
		this.context.lineTo(this.width, this.spacing * i);
	}
	for(var j = 0; j < this.numcols; j++) {
		this.context.moveTo(this.spacing * j, 0);
		this.context.lineTo(this.spacing * j, this.height);
	}	
	this.context.stroke();
	this.context.restore();
}

function hideForm() {
//	getID("predictionForm").
	document.getElementById("predictionForm").style.visibility="hidden";
}