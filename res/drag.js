/*
// dom drag functions 2011-04-26
*/

function getAbsolutePosition (e) {
	var width = e.offsetWidth;
	var height = e.offsetHeight;
	var left = e.offsetLeft;
	var top = e.offsetTop;
	while (e=e.offsetParent) {
		left += e.offsetLeft;
		top += e.offsetTop;
	};
	var right = left+width;
	var bottom = top+height;
	return {
		'width': width, 'height': height,
		'left': left, 'top': top,
		'right': right, 'bottom': bottom
	}
};

function getDocRect (wnd) {
	wnd = wnd || window;
	doc = wnd.document;
	return {
		left :doc.documentElement.scrollLeft||doc.body.scrollLeft||wnd.pageXOffset||wnd.scrollX||0+10,
		top :doc.documentElement.scrollTop||doc.body.scrollTop||wnd.pageYOffset||wnd.scrollY||0,
		width :doc.documentElement.clientWidth||doc.body.clientWidth||wnd.innerWidth||doc.width||0,
		height :doc.documentElement.clientHeight||doc.body.clientHeight||wnd.innerHeight||doc.height||0
	}
};

var elDrag = null;
var bLayout = true;
var isIE = !!window.ActiveXObject;
var bDraging = false;

var handlestart = function (evt, el) {
	var rect = getDocRect();
	var p = getAbsolutePosition(el);
	bDraging = true;
	evt = window.event||evt;
	elDrag = el;
	if (elDrag.setCapture) elDrag.setCapture();
	elDrag.onlosecapture = function() { handlestop(); }
	elDrag.deltaX = evt.clientX+rect.left-(bLayout?p.left:p.width);
	elDrag.deltaY = evt.clientY+rect.top-(bLayout?p.top:p.height);
};

var handledraging = function (evt) {
	if (!bDraging) return false;
	evt = window.event||evt;
	try {
		var rect = getDocRect();
		var x, y;
		x = evt.clientX+rect.left-elDrag.deltaX;
		y = evt.clientY+rect.top-elDrag.deltaY;
		if (bLayout) {
			if (evt.clientX>1 && evt.clientX<=rect.width)
			elDrag.style.left = x +'px';
			if (evt.clientY>1 && evt.clientY<=rect.height)
			elDrag.style.top = y +'px';
		} else {
			if (x>1 && evt.clientX<=rect.width)
			elDrag.style.width = x +'px';
			if (y>1 && evt.clientY<=rect.height)
			elDrag.style.height = y +"px";
		}
	} catch (ex) {};
};

var handlestop = function (evt) {
	evt = evt || window.event;
	if (!bDraging) return false;
	if (elDrag.releaseCapture) elDrag.releaseCapture();
	document.body.focus();
	bDraging = false;
};

document.onmousemove = handledraging;
document.onmouseup = handlestop;
