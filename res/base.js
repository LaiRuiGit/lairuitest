// --------------------------------------------------------
// - 功能说明 : JavaScript 函数库
// - 创建作者 : 小陈 
// - 创建时间 : 2013-04-20 05:00 => 2013-06-21 16:28
// --------------------------------------------------------
var nSelCount=0;

function byid(id) {
	return document.getElementById(id);
}

// 获取字符串（或任意类型）中包含的数字
function get_num(string) {
	var nums = '';
	string = '' + string;
	for (var i=0; i<string.length; i++) {
		var ch = string.substring(i, i+1);
		if (ch in [0,1,2,3,4,5,6,7,8,9]) {
			nums += ch;
		}
	}
	return nums;
}


function set_center(obj) {
	var objwidth = obj.offsetWidth;
	var objheight = obj.offsetHeight;
	var left = (document.documentElement.clientWidth - objwidth) / 2;
	obj.style.left = left+"px";
	var top = document.documentElement.scrollTop+(document.documentElement.clientHeight - objheight) / 2;
	obj.style.top = top+"px";
}

function get_position(what, offsettype) {
	var pos = {"left":what.offsetLeft, "top":what.offsetTop};
	var parentEl = what.offsetParent;
	while (parentEl != null) {
		pos.left += parentEl.offsetLeft;
		pos.top += parentEl.offsetTop;
		parentEl = parentEl.offsetParent;
	}
	if (offsettype) {
		return offsettype == "left" ? pos.left : pos.top;
	} else {
		return pos;
	}
}


function msg_box(string, showtime) {
	if (window.parent && window.parent.msg_box) {
		window.parent.msg_box(string, showtime);
	} else {
		alert(string);
	}
}

function set_title(obj, title_id) {
	var oti = byid(title_id);
	if (oti.value=="") {
		var s=obj.value; var isb=false; u="";
		for (ni=s.length; ni>=0; ni--) {c=s.charAt(ni);if(isb){if(c=='/'||c=='\\'||c=='_'||c=='.')break;u=c+u;}if(c=='.')isb=true;}
		oti.value=u;
	}
}

function select_all() {
	var ofm=document.forms["mainform"]; nSelCount=0;
	for(var i=0; i<ofm.elements.length; i++) {
		var e=ofm.elements[i];
		if(e.type=='checkbox'&&e.disabled!=true){e.checked=true; nSelCount++;}
	}
}

function select_none() {
	ofm=document.forms["mainform"]; nSelCount=0;
	for(var i=0; i<ofm.elements.length; i++) {
		var e=ofm.elements[i];
		if(e.type=='checkbox' && e.disabled!=true) {e.checked=false; nSelCount++;}
	}
}

function unselect() {
	ofm=document.forms["mainform"]; nSelCount = 0;
	for(var i=0; i<ofm.elements.length; i++) {
		var e = ofm.elements[i];
		if(e.type == 'checkbox' && e.disabled != true) {e.checked=!e.checked; nSelCount++;}
	}
}

function get_select() {
	ofm=document.forms["mainform"]; var u=''; nSelCount=0;
	for (var i=0; i<ofm.elements.length; i++) {
		var e = ofm.elements[i];
		if(e.type == 'checkbox' && e.checked == true && e.name != 'group'){if(nSelCount>0)u+=","; u+=e.value; nSelCount++;}
	}
	return u;
}

function insert() {
	window.location="?op=insert&r=" + Math.random();
}

function del() {
	cl=get_select();
	if (nSelCount == 0) {
		alert("您没有选择任何一条资料，无法执行删除！");
		return false;
	}
	if (!confirm("共选择了 "+nSelCount+" 条资料，您确定要删除吗？")) return false;
	window.location="?op=delete&id="+cl+"&r="+Math.random();
}

function set_show(n) {
	ofm=document.forms["mainform"]; cl=get_select();
	if (nSelCount == 0){alert("您没有选择任何一条资料！"); return false;}
	window.location="?op=setshow&id="+cl+"&value="+n+"&r="+Math.random();
}

function set_home(n) {
	ofm=document.forms["mainform"]; cl=get_select();
	if (nSelCount == 0){alert("您没有选择任何一条资料！"); return false;}
	window.location="?op=sethome&id="+cl+"&value="+n+"&r="+Math.random();
}

function set_check(cl, chk) {
	k=chk.checked; al=cl.split(','); ofm=document.forms["mainform"];
	for (ni=0; ni<al.length; ni++) {if (al[ni]) {s="ofm."+al[ni]+".checked="+k+";"; eval(s); }}
}

function set_parent_check(cl, chk) {
	k=chk.checked;
	if (k) {
		al=cl.split(','); ofm=document.forms["mainform"];
		for (ni=0; ni<al.length; ni++) {if (al[ni]) {s="ofm."+al[ni]+".checked="+k+";"; eval(s); }}
	}
}

function set_focus() {
	afm=document.getElementsByTagName("form");
	for (i=0; i<afm.length; i++) {
		ofm=afm[i]; ai=ofm.getElementsByTagName("input");
		for (ni=0; ni<ai.length; ni++) {oi=ai[ni];if(oi.name=="title") {oi.focus();return true;}}
	}
}

function mi(o) {
	var tds = o.getElementsByTagName("td");
	for (var i = 0; i < tds.length; i ++) {
		tds[i].style.backgroundColor = "#EBF5F5";
	}
}

function mo(o) {
	var tds = o.getElementsByTagName("td");
	for (var i = 0; i < tds.length; i ++) {
		tds[i].style.backgroundColor = "";
	}
}

// xmlhttp函数的封装
function ajax() {
	var xm,bC=false;
	try{xm=new ActiveXObject("Msxml2.XMLHTTP")}catch(e){try{xm=new ActiveXObject("Microsoft.XMLHTTP")}catch(e){try{xm=new XMLHttpRequest()}catch(e){xm=false}}}
	if(!xm)return null;this.connect=function(sU,sM,sV,fn){if(!xm)return false;bC=false;sM=sM.toUpperCase();
	try{if(sM=="GET"){xm.open(sM,sU+"?"+sV,true);sV=""}else{xm.open(sM,sU,true);
	xm.setRequestHeader("Method","POST "+sU+" HTTP/1.1");
	xm.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8")}
	xm.onreadystatechange=function(){if(xm.readyState==4&&!bC){bC=true;if(xm.status==200){fn(xm)}else{window.status="ajax error status code: "+xm.status}}};
	xm.send(sV)}catch(z){return false}return true};return this;
}

// json 返回值的解析处理:
function ajax_out(xm) {
	var s = xm.responseText;
	if (s == "") {
		alert("ajax返回结果为空.."); return {};
	}
	try {
		eval("var out="+s+";");
	} catch (e) {
		alert(s);
		return {};
	}

	return out;
}

function reg_event(obj, event_basename, fn) {
	if (document.all) {
		obj.attachEvent("on"+event_basename, fn);
	} else {
		obj.addEventListener(event_basename, fn, false);
	}
}

function isdel() {
	return confirm("您确定要删除该资料吗？");
}

function page_init() {

	if (window.page_is_init == true) {
		return;
	}
	window.page_is_init = true;

	window.document.onmouseover = function(e) {
		var event = e ? e : (window.event ? window.event : null);
		var o = event.srcElement ? event.srcElement : event.target;
		//parent.document.title = o.tagName;
		while (o.tagName != 'TD' && o.parentNode.tagName != 'HTML') {
			o = o.parentNode;
		}
		if (o.tagName == "TD" && (o.className == "item" || o.parentNode.className == "line")) {
			mi(o.parentNode);
		}
	}

	window.document.onmouseout = function(e) {
		var event = e ? e : (window.event ? window.event : null);
		var o = event.srcElement ? event.srcElement : event.target;
		while (o.tagName != 'TD' && o.parentNode.tagName != 'HTML') {
			o = o.parentNode;
		}
		if (o.tagName == "TD" && (o.className == "item" || o.parentNode.className == "line")) {
			mo(o.parentNode);
		}
	}

	// 表格效果:
	var etable = document.getElementsByTagName("table");
	for (var i=0; i<etable.length; i++) {
		// 对edit页面加动态效果:
		if (etable[i].className == "edit") {
			var etr = etable[i].getElementsByTagName("tr");
			for (var j=0; j<etr.length; j++) {
				var etd = etr[j].getElementsByTagName("td");
				for (var x=0; x<etd.length; x++) {
					if (in_class("left", etd[x].className)) {
						etd[x].style.backgroundColor = (j % 2 ? "#FCFCFC" : "#F6F6F6");
					}
					if (in_class("right", etd[x].className)) {
						etd[x].style.backgroundColor = (j % 2 ? "#FFFFFF" : "#F9F9F9");
					}
				}
			}
		}
	}

	// 按钮,输入框效果
	var eto = [];
	var einput = document.getElementsByTagName("input");
	for (var i=0; i<einput.length; i++) {
		if (einput[i].type == "text" || einput[i].type == "password" || einput[i].type == "file") {
			eto.push(einput[i]);
		}
	}
	var etextarea = document.getElementsByTagName("textarea");
	for (var i=0; i<etextarea.length; i++) {
		eto.push(etextarea[i]);
	}

	var eselect = document.getElementsByTagName("select");
	for (var i=0; i<eselect.length; i++) {
		eto.push(eselect[i]);
	}

	for (var i=0; i<eto.length; i++) {
		reg_event(eto[i], "focus", function(e) {
			var event = e ? e : (window.event ? window.event : null);
			var o = event.srcElement ? event.srcElement : event.target;
			if (o.className == "input") {
				o.className = "input_focus";
			}
		});
		reg_event(eto[i], "blur", function(e) {
			var event = e ? e : (window.event ? window.event : null);
			var o = event.srcElement ? event.srcElement : event.target;
			if (o.className == "input_focus") {
				o.className = "input";
			}
		});
	}

	// 按钮效果:
	var eto = [];
	var einput = document.getElementsByTagName("input");
	for (var i=0; i<einput.length; i++) {
		if (einput[i].type == "button" || einput[i].type == "submit") {
			eto.push(einput[i]);
		}
	}
	var ebutton = document.getElementsByTagName("button");
	for (var i=0; i<ebutton.length; i++) {
		eto.push(ebutton[i]);
	}

	for (var i=0; i<eto.length; i++) {
		var c = eto[i].className;
		if (in_class("button", c) || in_class("buttonb", c) || in_class("search", c) || in_class("submit", c)) {
			eto[i].onmouseover = function() {
				add_class(this, this.className.split(" ")[0]+"_over");
			}
			eto[i].onmouseout = function() {
				remove_class(this, this.className.split(" ")[0]+"_over");
			}
		}
	}

	var s = self.location.href.split("#")[1];
	if (s != '' && !isNaN(s) && byid("#"+s)) {
		byid("#"+s).className = "list_tr_modified";
		byid("#"+s).scrollIntoView(true);
	}
}

function in_class(class_name, obj_class) {
	var obj_class_s = obj_class.split(" ");
	for (var i=0; i<obj_class_s.length; i++) {
		if (obj_class_s[i] == class_name) {
			return true;
		}
	}
	return false;
}

function add_class(o, new_class) {
	var s = o.className;
	o.className = s ? s+" "+new_class : new_class;
}

function remove_class(o, class_name) {
	var s = o.className;
	if (s == class_name) {
		o.className = '';
	} else {
		var s_s = s.split(" ");
		var new_class = [];
		for (var i=0; i<s_s.length; i++) {
			if (s_s[i] != class_name) {
				new_class.push(s_s[i]);
			}
		}
		o.className = new_class.join(" ");
	}
}

function reg_event(obj, type, fn) {
	if(obj.attachEvent) {
		obj['e'+type+fn] = fn;
		obj[type+fn] = function(){obj['e'+type+fn](window.event);}
		obj.attachEvent('on'+type, obj[type+fn]);
	}else {
		obj.addEventListener(type, fn, false);
	}
}

function attach_event(evt, callback) {
	if (window.addEventListener) {
		window.addEventListener(evt, callback, false);
	} else if (window.attachEvent) {
		window.attachEvent("on" + evt, callback);
	}
}

function my_dom_ready(f) {
	if (my_dom_ready.done) return f();
	if (my_dom_ready.timer) {
		my_dom_ready.ready.push(f);
	} else {
		attach_event("load", my_is_dom_ready);
		my_dom_ready.ready = [f];
		my_dom_ready.timer = setInterval(my_is_dom_ready, 100);
	}
}

function my_is_dom_ready() {
	if (my_dom_ready.done) return false;
	if (document && document.getElementsByTagName && document.getElementById && document.body) {
		clearTimeout(my_dom_ready.timer);
		clearInterval(my_dom_ready.timer);
		my_dom_ready.timer = null;
		for ( var i = 0; i < my_dom_ready.ready.length; i++ ) {
			my_dom_ready.ready[i]();
		}
		my_dom_ready.ready = null;
		my_dom_ready.done = true;
	}
}


function set_item_color(obj) {
	if (obj.checked) {
		obj.parentNode.parentNode.className = "list_tr_checked";
	} else {
		obj.parentNode.parentNode.className = "";
	}
}


my_dom_ready(page_init);