(function() {
	"use strict";

  /**
   * Easy selector helper function
   */
  const select = (el, all = false) => {
    el = el.trim()
    if (all) {
      return [...document.querySelectorAll(el)]
    } else {
      return document.querySelector(el)
    }
  }

  /**
   * Easy event listener function
   */
  const on = (type, el, listener, all = false) => {
    if (all) {
      select(el, all).forEach(e => e.addEventListener(type, listener))
    } else {
      select(el, all).addEventListener(type, listener)
    }
  }

  /**
   * Easy on scroll event listener 
   */
  const onscroll = (el, listener) => {
    el.addEventListener('scroll', listener)
  }

  /**
   * Search bar toggle
   */
  if (select('.search-bar-toggle')) {
    on('click', '.search-bar-toggle', function(e) {
      select('.search-bar').classList.toggle('search-bar-show')
    })
  }

  /**
   * Navbar links active state on scroll
   */
  let navbarlinks = select('#navbar .scrollto', true)
  const navbarlinksActive = () => {
    let position = window.scrollY + 200
    navbarlinks.forEach(navbarlink => {
      if (!navbarlink.hash) return
      let section = select(navbarlink.hash)
      if (!section) return
      if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
        navbarlink.classList.add('active')
      } else {
        navbarlink.classList.remove('active')
      }
    })
  }
  window.addEventListener('load', navbarlinksActive)
  onscroll(document, navbarlinksActive)

  /**
   * Toggle .header-scrolled class to #header when page is scrolled
   */
  let selectHeader = select('#header')
  if (selectHeader) {
    const headerScrolled = () => {
      if (window.scrollY > 100) {
        selectHeader.classList.add('header-scrolled')
      } else {
        selectHeader.classList.remove('header-scrolled')
      }
    }
    window.addEventListener('load', headerScrolled)
    onscroll(document, headerScrolled)
  }

  /**
   * Back to top button
   */
  let backtotop = select('.back-to-top')
  if (backtotop) {
    const toggleBacktotop = () => {
      if (window.scrollY > 100) {
        backtotop.classList.add('active')
      } else {
        backtotop.classList.remove('active')
      }
    }
    window.addEventListener('load', toggleBacktotop)
    onscroll(document, toggleBacktotop)
  }
});

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