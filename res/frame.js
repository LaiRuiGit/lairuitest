// --------------------------------------------------------
// - 功能说明 : Frame框架函数
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-14 20:40 => 2013-03-30 10:38
// --------------------------------------------------------
var s_split = "<img src='res/img/word_spacer.gif' width='7' height='15' align='absmiddle'>";
var click_link = "javascript:void(0);";
var navi_pre = "<font color='red'>您的位置:</font> ";
var loading_pre = "<img src='res/img/loading.gif' width='16' height='16' align='absmiddle'> ";
var menu_max_char = Array();

function byid(id) {
	return document.getElementById(id);
}

function byname(name) {
	return document.getElementsByTagName(name);
}

function preload(image_list) {
	var im_count = image_list.length;
	var im = new Array();
	for (var ni=0; ni<im_count; ni++) {
		im[ni] = new Image();
		im[ni].src = "./res/img/" + image_list[ni];
	}
}

function init_top_menu() {
	var a_menu = Array();
	var ni = 0;
	for (var i in menu_mids) {
		var mid = menu_mids[i];
		if (menu_data[mid]) {
			var has_sub_menu = menu_stru[mid] != "";
			if (has_sub_menu) {
				a_menu[ni] = "<a id='mt"+mid+"' href='"+click_link+"' onclick='load("+menu_stru[mid][0]+");return false'"; //加载第一个子菜单的链接
			} else {
				a_menu[ni] = "<a id='mt"+mid+"' href='"+click_link+"' onclick='load("+mid+");return false'";
			}
			if (show_dyn_menu && has_sub_menu) {
				a_menu[ni] += " onmouseover='dropdownmenu(this, event, menu"+mid+", \"150px\", "+mid+")' onmouseout='delayhidemenu()'";
			}
			a_menu[ni] += " onfocus='this.blur();'>"+menu_data[mid][0]+"</a>";
			ni++;

			if (show_dyn_menu && has_sub_menu) {
				eval("menu"+mid+"=Array();");
				var cnt = 0;
				var max_char = 0;
				for (var nm in menu_stru[mid]) {
					eval("menu"+mid+"["+cnt+"]=\"<a href='"+click_link+"' onclick='load("+menu_stru[mid][nm]+");return false'>"+menu_data[menu_stru[mid][nm]][0]+"</a>\";");
					cnt++;
					if (menu_data[menu_stru[mid][nm]][0].length > max_char) {
						max_char = menu_data[menu_stru[mid][nm]][0].length;
					}
				}
				menu_max_char[mid] = max_char;
			}
		}
	}
	byid("sys_top_menu").innerHTML = a_menu.join(s_split);
}

function load(mid) {
	var is_load_url = (arguments.length == 1 ? 1 : arguments[1]);

	top_level_mid = get_parent_mid(mid);
	mid_is_top = !(top_level_mid > 0);
	top_level_mid = mid_is_top ? mid : top_level_mid;

	// 顶部当前菜单加红显示:
	var e = byid("sys_top_menu").getElementsByTagName("a");
	for (var i in e) {
		e[i].className = e[i].id == ("mt"+top_level_mid) ? "red" : "";
		if (e[i].id == ("mt"+top_level_mid)) {
			high_light_obj = e[i];
		}
	}

	// 建立左侧链接:
	var has_sub_menu = menu_stru[top_level_mid].length;
	if (has_sub_menu && menu_data[top_level_mid]) {
		var left_menu = "<table class='leftmenu_1'><tr><td class='head'>"+menu_data[top_level_mid][0]+"</td></tr>";
		for (var nm in menu_stru[top_level_mid]) {
			left_menu += "<tr><td class='item' onmouseover='mi(this)' onmouseout='mo(this)'><a id='ml"+menu_stru[top_level_mid][nm]+"' href='"+click_link+"' onclick='load("+menu_stru[top_level_mid][nm]+");return false' class=''>"+menu_data[menu_stru[top_level_mid][nm]][0]+"</a></td></tr>";
		}
		left_menu += "</table>";
		byid("sys_left_menu").innerHTML = left_menu;
		byid("sys_left_menu").style.display = "block";
		if (!mid_is_top) {
			byid("ml"+mid).className = "red";
		}
	} else {
		byid("sys_left_menu").innerHTML = '';
		byid("sys_left_menu").style.display = "none";
	}

	// 建立快捷菜单:
	if (show_shortcut && menu_shortcut) {
		var shortcut_tmp = "<table class='leftmenu_2'><tr><td class='head'>快捷方式</td></tr>";
		for (var ni in menu_shortcut) {
			item_mid = menu_shortcut[ni];
			if (!menu_data[item_mid] || (get_parent_mid(item_mid) == top_level_mid)) {
				continue;
			}
			shortcut_tmp += "<tr><td class='item' onmouseover='mi(this)' onmouseout='mo(this)'><a id='ms"+item_mid+"' href='"+click_link+"' onclick='load("+item_mid+","+get_parent_mid(item_mid)+");return false' class=''>"+menu_data[item_mid][0]+"</a></td></tr>";
		}
		byid("sys_shortcut").innerHTML = shortcut_tmp;
		byid("sys_shortcut").style.display = "block";
	} else {
		byid("sys_shortcut").innerHTML = '';
		byid("sys_shortcut").style.display = "none";
	}

	// 加载当前页面:
	if (is_load_url && menu_data[mid][1]) {
		if (mid_is_top) {
			make_navi(menu_data[mid][0]);
		} else {
			make_navi(menu_data[top_level_mid][0]+','+menu_data[mid][0]);
		}

		show_status("加载中，请稍候...");
		byid("sys_frame").mid = mid;
		byid("sys_frame").src = menu_data[mid][1];
		byid("sys_frame").framesrc = menu_data[mid][1];
		location.replace(location.href.split('#')[0]+"#"+mid);
		msg_box_hide();

		//oAjax = new ajax();
		//oAjax.connect("http/menu_click_count.php", "GET", "mid="+mid+"&r="+Math.random(), function(){});
	}
}

function load_url(url, navi) {
	show_status("页面加载中，请稍候...");

	make_navi(navi);
	byid("sys_frame").mid = 0;
	byid("sys_frame").src = url;
	byid("sys_frame").framesrc = url;
	msg_box_hide();
	byid("sys_frame").onreadystatechange = function() {update_navi(1);}
}

function get_parent_mid(mid) {
	for (var pmid in menu_stru)
		for (var nm in menu_stru[pmid])
			if (menu_stru[pmid][nm] == mid)
				return pmid;
	return 0;
}

function show_status(string) {
	var o = byid("sys_loading");
	if (string != '') {
		byid("sys_loading_tip").innerHTML = string;
		o.style.display = "block";
		byid("sys_loading").style.left = get_position(byid("logo_bar"), "left") + byid("logo_bar").offsetWidth - byid("sys_loading").offsetWidth - 3 + "px";
		byid("sys_loading").style.top = get_position(byid("logo_bar"), "top") + byid("logo_bar").offsetHeight - byid("sys_loading").offsetHeight - 1 + "px";
	} else {
		o.style.display = "none";
		byid("sys_loading_tip").innerHTML = '';
	}
}

function frame_loaded_do(oframe) {
	if (window.frame_base_height) {
		oframe.style.height = window.frame_base_height+"px";
	}
	show_status('');
}

function clk(obj) {
	// ...
}

function frame_auto_height() {
	var iframe = document.getElementById("sys_frame");
	try {
		var bHeight = iframe.contentWindow.document.body.scrollHeight;
		var dHeight = iframe.contentWindow.document.documentElement.scrollHeight;
		var height = Math.max(bHeight, dHeight);
		iframe.style.height = height+"px";

		// make message box always in center 2013-04-06 13:03
		if (byid("sys_msg_box").style.display == "block") {
			set_center(byid("sys_msg_box"));
		}
	} catch (ex) {
		//...
	}
}

function update_navi(is_focus) {
	//if (byid("sys_frame").readyState == "loading") {
		//byid("sys_frame").style.height = window.frame_base_height+"px";
	//}

	if (byid("sys_frame").readyState == "complete") {
		//alert("wait 1");
		//alert();
		if (typeof(byid("sys_frame").contentWindow.location.href) == typeof('')) {
			var real_src = byid("sys_frame").contentWindow.location.href;
		} else {
			var real_src = byid("sys_frame").src;
		}
		//alert(real_src);
		var frame_src = byid("sys_frame").framesrc;
		//alert(frame_src);
		if (typeof(real_src) == typeof('')) {
			real_src = real_src.split('/').reverse()[0];
			if (is_focus || real_src != frame_src) {
				var local_findit = false;
				for (var mid in menu_data) {
					if (menu_data[mid][1] == real_src) {
						byid("sys_frame").mid = mid;
						var findit = false;
						for (var main_id in menu_stru) {
							if (main_id == mid) {
								update_navi_status(main_id, 0, menu_data[main_id][0]);
								local_findit = true;
								break;
							} else {
								for (var nm in menu_stru[main_id]) {
									item_id = menu_stru[main_id][nm];
									if (item_id == mid) {
										update_navi_status(main_id, item_id, menu_data[main_id][0]+","+menu_data[item_id][0]);
										local_findit = findit = true;
										break;
									}
								}
								if (findit) {
									break;
								}
							}
						}
						break;
					}
				}

				// If not find it,request to server
				if (!local_findit) {
					oAjax = new ajax();
					oAjax.connect("http/get_page_info.php", "GET", "p="+escape(real_src)+"&r="+Math.random(), update_navi_do);
				}
			}
		}
	}
}

function update_navi_do(oAjax) {
	try {eval("var aNaviInfo="+oAjax.responseText+";");} catch (e) {return false;}
	if (typeof(byid("sys_frame").contentWindow.location.href) == typeof('')) {
		var src = byid("sys_frame").contentWindow.location.href;
	} else {
		var src = byid("sys_frame").src;
	}
	var now_url = src.split('/').reverse()[0];
	if (aNaviInfo["url"] == now_url) {
		update_navi_status(aNaviInfo["top_mid"], aNaviInfo["left_mid"], aNaviInfo["navi"]);
	}
}

function update_navi_status(top_mid, left_mid, navi_string) {
	if (top_mid > 0 || left_mid > 0) {
		var now_url = byid("sys_frame").contentWindow.location.href.split('/').reverse()[0];
		byid("sys_frame").framesrc = now_url;
		make_navi(navi_string);
		load(((left_mid > 0 && menu_data[left_mid]) ? left_mid : top_mid), 0);
	}
}

function make_navi(string) {
	var navi_split = " → ";
	var title_array = ('管理后台,'+string).split(',');
	for (var n in title_array) {
		title_array[n] = '<b>'+title_array[n]+'</b>';
	}
	//byid("sys_navi").innerHTML = navi_pre+title_array.join(navi_split);
}

function load_js_file(src, id, loaded_fn) {
	var headerDom = document.getElementsByTagName('head').item(0);
	var jsDom = document.createElement('script');
	jsDom.type = 'text/javascript';
	jsDom.src = src;
	if (id) {
		jsDom.id = id;
	}

	headerDom.appendChild(jsDom);

	if (loaded_fn) {
		if (!document.all) {
			jsDom.onload = function () {
				loaded_fn();
			}
		} else {
			jsDom.onreadystatechange = function () {
				if (jsDom.readyState == 'loaded' || jsDom.readyState == 'complete') {
					loaded_fn();
				}
			}
		}
	}
}


// 在线信息:
function get_online() {
	if (byid("js_online_info")) {
		byid("js_online_info").parentNode.removeChild(byid("js_online_info"));
	}

	//load_js_file("http/get_online.php?r="+Math.random(), "js_online_info");

	//online_last_send_time = new Date().getTime();

	//online_error_timer = setTimeout("get_online_error()", 15000); //15s超时
}

// 在线信息处理结果:
function get_online_do(out) {
	//document.title = "⊙顾客管理  "+new Date().toTimeString().substring(0,8)+" "+((new Date().getTime() - online_last_send_time) / 1000);
	document.title = ori_doc_title;
	clearTimeout(online_error_timer); //立即停止调用无响应函数

	//var out = ajax_out(o);
	if (out["status"] == 'logout') {
		show_server_logout();
		return;
	}

	setTimeout("get_online()", 10000); //继续下一次请求

	if (out["status"] == 'ok') {
		if (out["online_list"]) {
			show_online_list(out["online_list"]);
		}
		// 包括了在线通知和在线消息
		if (out["online_notice"]) {
			//show_online_message(out["online_message"]);
			show_online_notice(out["online_notice"]);
		} else {
			byid("sys_notice").innerHTML = '';
		}
		if (out["alert"]) {
			msg_box(out["alert"], 3); //显示消息
		}
	}
}

// 服务器超时提示:
function show_server_logout() {
	alert("服务器端已经退出，请您重新登录！");
	top.location = "m/login.php";
}

// 获取在线消息错误:
function get_online_error() {
	document.title = "请求30s超时";
	get_online();
}


// 现在在线用户列表:
function show_online_list(aOnline) {
	if (typeof(aOnline) == typeof(window)) {
		var string = "<table width='100%' class='leftmenu_online'>";
		string += "<tr><td class='head'><div style='float:left;'>在线用户</div><div style='float:right;'><a href='javascript:void(0);' onclick=\"load_url('m/sys/online_all.php')\" title='显示所有在线用户'>更多>></a></div><div class='clear'></div></td></tr>";
		for (var n in aOnline) {
			string += "<tr><td class='item' onmouseover='mi(this)' onmouseout='mo(this)'>";
			if (n > 0) {
				string += "<a href='#"+n+":"+aOnline[n]["name"]+"' onclick=\"load_box(1, 'src', 'm/sys/admin.php?op=viewweb&name="+aOnline[n]["name"]+"'); return false;\" title='"+(aOnline[n]["isowner"] != 1 ? "查看用户资料" : "查看我的资料")+"'>";
			}
			string += aOnline[n]["realname"];
			if (n > 0) {
				string += "</a>";
			}
			if (aOnline[n]["isowner"] != 1) {
				string += "&nbsp;<a href='javascript:void(0)' onclick=\"load_box(1, 'src', 'm/sys/talk.php?to="+n+"');\" class='talk' title='点击发送消息'>[交谈]</a>";
			}
			string + "</td></tr>";
		}
		string += "</table>";
		
		
	} else {
		var string = "<div class='online_tips'>没有其他用户在线</div>";
	}
	if (typeof(byid("sys_online")) == typeof(window) && string) {
		byid("sys_online").innerHTML = string;
	}
}

function show_online_notice(arr) {
	if (typeof(arr) == typeof(window)) {
		var s = '';
		if (arr) {
			s += '<table width="100%" class="leftmenu_online">';
			s += '<tr><td class="head">通知和消息</td></tr>';
			for (var k in arr) {
				var li = arr[k];
				s += '<tr><td class="item" onmouseover="mi(this)" onmouseout="mo(this)">';
				if (li["type"] == "notice") {
					li["title"] = '<font color=red>'+li["title"]+'</font>';
					// 处理某些东西
				} else if (li["type"] == "message") {
					// 处理某些东西
				}
				s += '<a href="javascript:void(0);" onclick="load_box(1, \'src\', \''+li["url"]+'\');">'+li["title"]+'</a>';
				s += '</td></tr>';
			}
			s += '</table>';
		}
		byid("sys_notice").innerHTML = s;
	}
}


// 暂时不用了 2010-09-16 16:45
// 显示收到的消息:
function show_online_message(aMess) {
	if (typeof(aMess) == typeof(window)) {
		var string = '';
		for (var mid in aMess) {
			if (!mid) continue;
			string += "<table width='100%' id='online_mess_" + mid + "' style='border:1px solid #E1E1E1'><tr><td width='75%' style='padding:3px 6px 3px 6px' style='color:#000000'>" + aMess[mid]["time"] + "&nbsp;<font color='#FF5F11'><b>" + aMess[mid]["realname"] + "</b></font> 说：</td><td width='25%' align='right' style='padding-right:6px'><a href='#' onclick=\"online_talk('" + aMess[mid]["fromname"] + "','" + aMess[mid]["realname"] + "'," + mid + ");return false\" class='talk_op'>[回复]</a>&nbsp;<a href='#' onclick='online_talk_close(" + mid + ");return false' title='我已经阅读,不要再显示' class='talk_op'>[关闭]</a></td></tr><tr><td colspan='2' style='padding:6px' style='color:#000000'>";
			if (aMess[mid]["link"] != '') {
				string += "<a href='"+aMess[mid]["link"]+"' target='main'><b>" + aMess[mid]["content"] + "</b></a>";
			} else {
				string += "<b>" + aMess[mid]["content"] + "</b>";
			}
			string += "</td></tr></table>";
		}

		var obj = byid("online_message");
		if (string && typeof(obj) == typeof(window)) {
			obj.innerHTML += string;
			obj.style.display = "block";
			var wsize = get_size();
			var psize = get_scroll();
			obj.style.top = psize[1] + wsize[3] - obj.offsetHeight - 5 + "px";
			obj.style.left = wsize[0] - obj.offsetWidth - 20 + "px";

			play_music("media/havemess.mp3");
		}
	}
}

// 消息提示位置控制:
function message_list_keep_position() {
	var obj = byid("online_message");

	// 显示的有消息时才执行:
	if (obj.style.display == "block" && obj.innerHTML != '') {
		var wsize = get_size();
		var psize = get_scroll();
		obj.style.top = psize[1] + wsize[3] - obj.offsetHeight - 5 + "px";
		obj.style.left = wsize[0] - obj.offsetWidth - 20 + "px";
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

function get_online_list() {
	var obj = document.getElementById("sys_online");
	if (obj.innerHTML == "") {
		obj.innerHTML = "<div class='online_tips'>正在读取在线用户...</div>";
	}
	oAjax = new ajax();
	oAjax.connect("http/get_online_list.php", "GET", "r="+Math.random(), get_online_list_do);
}

//function get_online_list_do(oAjax) {
//	try {eval("var aOnline="+oAjax.responseText+";");} catch (e) {return false; }
//	if (typeof(aOnline) == typeof(window)) {
//		var string = "<table width='100%' class='leftmenu_online'>";
//		string += "<tr><td class='head'>在线用户</td></tr>";
//		for (var n in aOnline) {
//			string += "<tr><td class='item' onmouseover='mi(this)' onmouseout='mo(this)'>";
//			string += "<a href='#' onclick=\"load_url('sys_admin_view.php?name="+n+"','在线用户,用户资料'); return false;\" title='"+(aOnline[n]["isowner"] != 1 ? "查看用户资料" : "查看我的资料")+"'>"+aOnline[n]["realname"]+"</a>";
//			if (aOnline[n]["isowner"] != 1) {
//				string += "&nbsp;<a href='#' onclick=\"online_talk('"+n+"','"+aOnline[n]["realname"]+"');return false;\" class='talk' title='点击发送消息'>[交谈]</a>";
//			}
//			string + "</td></tr>";
//		}
//		string += "</table>";
//	} else {
//		var string = "<div class='online_tips'>没有其他用户在线</div>";
//	}
//	if (typeof(byid("sys_online")) == typeof(window) && string) {
//		byid("sys_online").innerHTML = string;
//	}
//}

function online_talk(username, realname, messid) {
	if (messid == undefined) messid = 0;
	var string = "<table width='100%' border='0' style='border:0px solid #E6E6E6' height='100%'>";
	string += "<tr><td colspan='2' style='padding:4px 3px 2px 6px'>给 <font color='red'><b>" + realname + "</b></font> 发送消息:&nbsp;&nbsp;&nbsp;<a href='#' onclick='show_face(this);return false;'>[表情]</a></td></tr>";
	string += "<tr><td width='20%' align='center'>内容：</td><td width='80%'><textarea id='online_talk_content' style='width:90%;height:80px;' class='input'></textarea></td></tr>";
	string += "<tr><td colspan='2' height='60' align='center'><input type='button' class='submit' onclick=\"online_talk_submit('" + username + "',"+messid+")\" value='发送消息'>&nbsp;&nbsp;<input type='button' class='submit' onclick='online_talk_hide()' value='取消'></td></tr>";
	string += "</table>";

	//screen_lock();
	//var width = 400; var height = 165;
	//var left = (document.body.clientWidth - width) / 2;
	//var top = document.body.scrollTop + (document.body.clientHeight - height) / 2;
	//dialog_show(string, width, height, left, top);
	//byid("online_talk_content").focus();
	load_box(1, "str", string, "发送消息");
}

function show_face(obj) {
	var odiv = byid("sys_face_list");
	if (odiv && odiv.style.display == "block") {
		odiv.style.display = "none"; return false;
	}
	if (!odiv) {
		var f = "<table border='1' bordercolor='#E7E7E7' cellpadding='1'>";
		var face_count = 135;
		for (var x=0; x<9; x++) {
			f += "<tr>";
			for (var y=0; y<15; y++) {
				var n = x*15 + y;
				f += "<td>";
				if (n < face_count) {
					f += "<img src='image/face/"+n+".gif' onclick='write_face("+n+")' style='cursor:pointer'>";
				}
				f += "</td>";
			}
			f += "</tr>";
		}
		f += "</table>";

		var odiv = document.createElement("div");
		odiv.id = "sys_face_list";
		odiv.style.border = "2px solid gray";
		odiv.style.backgroundColor = "white";
		odiv.style.zIndex = "1000";
		odiv.style.position = "absolute";
		odiv.style.left = get_position(obj, "left") - 100;
		odiv.style.top = get_position(obj, "top") + obj.offsetHeight + 3;
		//odiv.style.zIndex = "6";
		odiv.innerHTML = f;
		document.body.appendChild(odiv);
	}
	odiv.style.display = "block";
}

function write_face(face_num) {
	byid("online_talk_content").value += "["+face_num+"]";
	byid("sys_face_list").style.display = "none";
}

function online_talk_submit(username, messid) {
	var obj = byid("online_talk_content");
	var content = obj.value;
	if (content == "") {
		alert("请输入发送消息的内容！");
		obj.focus();
		return false;
	}
	obj.value = "";
	online_talk_hide();
	oAjax = new ajax();
	oAjax.connect("http/online_talk_submit.php", "POST", "messid="+messid+"&name="+username+"&content="+content, online_talk_submit_do);
}

function online_talk_submit_do(oAjax) {
	var s = oAjax.responseText;
	if (s != "") {
		if (s > 0) {
			online_talk_close(s);
		}
	} else {
		alert("消息发送失败...");
	}
}

function online_talk_hide() {
	parent.load_box(0);
}

function get_online_messages() {
	var lastchecktime = typeof(online_message_lastchecktime) == typeof(0) ? online_message_lastchecktime : 0;
	oAjax = new ajax();
	oAjax.connect("http/get_online_message.php", "GET", "t="+lastchecktime+"&r="+Math.random(), get_online_messages_do);
}

function get_online_messages_do(oAjax) {
	try {eval("var aMess="+oAjax.responseText+";");} catch (e) {return false;}
	if (typeof(aMess) == typeof(window)) {
		var string = '';
		for (var mid in aMess) {
			if (!mid) continue;
			string += "<table width='100%' id='online_mess_" + mid + "' style='border:1px solid #E1E1E1'><tr><td width='75%' style='padding:3px 6px 3px 6px' style='color:#000000'>" + aMess[mid]["time"] + "&nbsp;<font color='#FF5F11'><b>" + aMess[mid]["realname"] + "</b></font> 说：</td><td width='25%' align='right' style='padding-right:6px'><a href='#' onclick=\"online_talk('" + aMess[mid]["fromname"] + "','" + aMess[mid]["realname"] + "'," + mid + ");return false\" class='talk_op'>[回复]</a>&nbsp;<a href='#' onclick='online_talk_close(" + mid + ");return false' title='我已经阅读,不要再显示' class='talk_op'>[关闭]</a></td></tr><tr><td colspan='2' style='padding:6px' style='color:#000000'><a href='"+aMess[mid]["link"]+"' target='main'><b>" + aMess[mid]["content"] + "</b></a></td></tr></table>";
		}
		var obj = byid("online_message");
		if (string && typeof(obj) == typeof(window)) {
			obj.innerHTML += string;
			obj.style.display = "block";
			play_music("media/havemess.mp3");
			obj.style.top = document.body.scrollTop + document.body.clientHeight - obj.offsetHeight - 5 + "px";
			obj.style.left = document.body.clientWidth - obj.offsetWidth - 20 + "px";
		}
	}
}

function online_talk_close(nMessID) {
	oAjax = new ajax();
	oAjax.connect("http/online_message_close.php", "GET", "id="+nMessID, online_talk_close_do);
}

function online_talk_close_do(oAjax) {
	var sMessID = oAjax.responseText;
	if (sMessID != "") {
		close_message(sMessID);
	}
}

function close_message(sMessID) {
	var obj = document.getElementById("online_mess_"+sMessID);
	obj.style.display = "none";
	obj = document.getElementById("online_message");
	if (obj.offsetHeight < 10) {
		obj.style.display = "none";
	}
}

function play_music(file) {
	if (file != "") {
		om = byid("sys_music_player");
		om.filename = file;
		om.play();
	}
}


function browser_info() {
	var ua, s, i;this.isIE = false;this.isNS = false;this.isOP = false;this.isSF = false;ua = navigator.userAgent.toLowerCase();s = "opera";if ((i = ua.indexOf(s)) >= 0){this.isOP = true;return;}s = "msie";if ((i = ua.indexOf(s)) >= 0) {this.isIE = true;return;}s = "netscape6/";if ((i = ua.indexOf(s)) >= 0) {this.isNS = true;return;}s = "gecko";if ((i = ua.indexOf(s)) >= 0) {this.isNS = true;return;}s = "safari";if ((i = ua.indexOf(s)) >= 0) {this.isSF = true;return;}
}

function screen_lock() {
	var browser = new browser_info();
	var objScreen = byid("sl_screen_over");
	if(!objScreen) var objScreen = document.createElement("div");
	var oS = objScreen.style;
	objScreen.id = "sl_screen_over";
	oS.display = "block";
	oS.top = oS.left = oS.margin = oS.padding = "0px";
	if (document.body.scrollHeight)	{
		var wh = (document.body.scrollHeight < document.body.clientHeight ? document.body.clientHeight : document.body.scrollHeight) + "px";
	}else if (window.innerHeight) {
		var wh = window.innerHeight + "px";
	} else {
		var wh = "100%";
	}
	oS.width = document.body.clientWidth;
	oS.height = wh;
	oS.position = "absolute";
	oS.zIndex = "3";
	if ((!browser.isSF) && (!browser.isOP)) {
		oS.background = "#B0B0B0";
	}else{
		oS.background = "#B4B4B4";
	}
	oS.filter = "alpha(opacity=50)";
	oS.opacity = 40/100;
	oS.MozOpacity = 40/100;
	document.body.appendChild(objScreen);
	set_select_visible(0);
}

function set_select_visible(show) {
	var visible = show ? "visible" : "hidden";
	var allselect = byname("select");
	for (var i=0; i<allselect.length; i++) {
		allselect[i].style.visibility = visible;
	}
	var frms = byname("iframe");
	for (var i=0; i<frms.length; i++) {
		var allselect = frms[i].contentWindow.document.getElementsByTagName("select");
		for (var j=0; j<allselect.length; j++) {
			allselect[j].style.visibility = visible;
		}
	}
}

function screen_clean() {
	var objScreen = document.getElementById("sl_screen_over");
	if (objScreen) objScreen.style.display = "none";
	set_select_visible(1);
}

function dialog_show(showdata,width,height,left,top) {
	var objDialog = document.getElementById("sl_dialog_move");if (!objDialog) objDialog = document.createElement("div");objDialog.id = "sl_dialog_move";var oS = objDialog.style;oS.display = "block";oS.top = top + "px";oS.left = left + "px";oS.margin = "0px";oS.padding = "0px";oS.width = width + "px";oS.height = height + "px";oS.position = "absolute";oS.zIndex = "5";oS.background = "#FFF";oS.border = "2px solid #838383";objDialog.innerHTML = showdata;document.body.appendChild(objDialog);
}

function dialog_hide() {
	screen_clean();var objDialog = document.getElementById("sl_dialog_move");if (objDialog) objDialog.style.display = "none";
}

function msg_box(string, showtime) {
	omsg = byid("sys_msg_box");
	if (string == undefined || string == "") {
		return true;
	}
	if (typeof(showtime) == "undefined") {
		var showtime = 5;
	} else {
		if (typeof(showtime) != typeof(0)) showtime *= 1;
		showtime = Math.min(20, Math.max(1, showtime));
	}
	byid("sys_msg_box_content").innerHTML = string;
	omsg.style.display = "block";
	set_center(omsg);
	sys_msg_box_timer = setTimeout("msg_box_hide()", showtime*1000);
}

function msg_box_hold() {
	clearInterval(sys_msg_box_timer);
}

function msg_box_delay_hide(time) {
	clearInterval(sys_msg_box_timer);
	if (typeof(time) == "undefined") {
		time = 1;
	} else {
		if (typeof(time) != typeof(0)) time *= 1;
		time = Math.min(20, Math.max(1, time));
	}
	sys_msg_box_timer = setTimeout("msg_box_hide()", time*1000);
}

function msg_box_hide() {
	omsg = byid("sys_msg_box");
	omsg.style.display = "none";
}

function set_center(obj) {
	var objw = obj.offsetWidth;
	var objh = obj.offsetHeight;
	var pscroll = get_scroll();
	var psize = get_size();
	var left = (psize[0] - objw) / 2;
	var top = pscroll[1] + (psize[3] - objh) / 2;
	obj.style.left = left < 0 ? "0px" : left+"px";
	obj.style.top = top < 0 ? "0px" : top+"px";
}

function mi(o) {
	o.style.backgroundColor = "#edfaf2";
}

function mo(o) {
	o.style.backgroundColor = "";
}

function set_body_height() {
	var all = get_size()[3];
	var main_bar_height = all - byid("top_border").offsetHeight - byid("logo_bar").offsetHeight - byid("menu_bar").offsetHeight - byid("bottom_border").offsetHeight - 6; // 6 是上下padding值

	var frame_base_height = main_bar_height - 0; //iframe的基准高度(刚好填充页面的高度)
	byid("frame_content").style.height = frame_base_height+"px";
	byid("sys_frame").style.height = frame_base_height+"px";

	// debug:
	//document.title = all + ", "+main_bar_height + ", "+frame_base_height+", "+byid("bottom_border").offsetHeight;
}

function get_size() {
	var xScroll, yScroll;
	if (window.innerHeight && window.scrollMaxY) {
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}

	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}

	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else {
		pageHeight = yScroll;
	}

	if(xScroll < windowWidth){
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}

	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight)
	return arrayPageSize;
}

function get_scroll() {
	var yScroll;
	if (self.pageYOffset) {
		yScroll = self.pageYOffset;
	} else if (document.documentElement && document.documentElement.scrollTop){	 // Explorer 6 Strict
		yScroll = document.documentElement.scrollTop;
	} else if (document.body) {// all other Explorers
		yScroll = document.body.scrollTop;
	}

	arrayPageScroll = new Array('',yScroll)
	return arrayPageScroll;
}

function show_hide_side() {
	if (byid("side_menu").style.display == "none") {
		byid("side_menu").style.display = "";
		byid("frame_content").style.marginLeft = 196;
	} else {
		byid("side_menu").style.display = "none";
		byid("frame_content").style.marginLeft = 0;
	}
}


function st(str) {
	str = window.status + " ==> "+str;
	if (str.length > 100) {
		str = str.substring(str.length - 100, str.length);
	}
	window.status = str;
}

var dom_loaded = {
	onload: [],
	loaded: function() {
		if (arguments.callee.done) return;
		arguments.callee.done = true;
		for (i = 0;i < dom_loaded.onload.length;i++) dom_loaded.onload[i]();
	},
	load: function(fireThis) {
		this.onload.push(fireThis);
		if (document.addEventListener)
			document.addEventListener("DOMContentLoaded", dom_loaded.loaded, null);
		if (/KHTML|WebKit/i.test(navigator.userAgent)) {
			var _timer = setInterval(function() {
				if (/loaded|complete/.test(document.readyState)) {
					clearInterval(_timer);
					delete _timer;
					dom_loaded.loaded();
				}
			}, 10);
		}
		/*@cc_on @*/
		/*@if (@_win32)
		var proto = "src='javascript:void(0)'";
		if (location.protocol == "https:") proto = "src=//0";
		document.write("<scr"+"ipt id=__ie_onload defer " + proto + "><\/scr"+"ipt>");
		var script = document.getElementById("__ie_onload");
		script.onreadystatechange = function() {
			if (this.readyState == "complete") {
				dom_loaded.loaded();
			}
		};
		/*@end @*/
		window.onload = dom_loaded.loaded;
	}
};

function swap_node(node1_name, node2_name) {
	var node1 = byid(node1_name);
	var node2 = byid(node2_name);

	var _parent = node1.parentNode;

	var o = _parent.childNodes;
	var _t1 = null, _t2 = null;
	for (var i=0; i<o.length; i++) {
		if (o[i].id == node1_name && i < o.length-1) {
			_t1 = o[i+1];
		}
		if (o[i].id == node2_name && i < o.length-1) {
			_t2 = o[i+1];
		}
	}

	if (_t1) {
		_parent.insertBefore(node2, _t1);
	} else {
		_parent.appendChild(node2);
	}
	if (_t2) {
		_parent.insertBefore(node1, _t2);
	} else {
		_parent.appendChild(node1);
	}
}

function co(obj, ty) {
	obj.style.backgroundColor = ty == 1 ? "#28D067" : "";
}


// 显示 div:
// type = "src|str"  "src":iframe.src, "str":string, innerHTML
function load_box(isshow, type, src_or_str, params_or_title) {
	if (isshow) {
		var wsize = get_size();
		var width = wsize[0];
		var height = Math.max(wsize[1], wsize[3]);

		byid("dl_layer_div").style.top = byid("dl_layer_div").style.left = "0px";
		byid("dl_layer_div").style.width = width+"px";
		byid("dl_layer_div").style.height = height+"px";
		byid("dl_layer_div").style.display = "block";

		byid("dl_box_div").style.left = (width-584)/2;
		byid("dl_box_div").style.top = 150;
		byid("dl_box_div").style.width = 600 + "px";
		byid("dl_box_div").style.display = "block";

		byid("dl_iframe").style.display = byid("dl_content").style.display = "none";
		if (type == "src") {
			setTimeout(function() {byid("dl_set_iframe").src = src_or_str+(params_or_title ? "?"+params_or_title : '');}, 20);
			byid("dl_box_loading").style.display = "block";
			byid("dl_box_title").innerHTML = "加载中...";
			byid("dl_box_div").style.height = byid("dl_box_title_box").offsetHeight + byid("dl_box_loading").offsetHeight + "px";
			timer_box = setInterval("reset_iframe_size()", 100);
			//timer_box = setTimeout("reset_iframe_size()", 1000);
		} else {
			byid("dl_content").innerHTML = src_or_str;
			byid("dl_content").style.display = "block";
			byid("dl_box_loading").style.display = "none";
			byid("dl_box_title").innerHTML = params_or_title;
			byid("dl_box_div").style.height = byid("dl_box_title_box").offsetHeight + byid("dl_content").offsetHeight + "px";
		}

		set_center(byid("dl_box_div"));

	} else {
		byid("dl_layer_div").style.display = "none";
		byid("dl_box_div").style.display = "none";
		byid("dl_set_iframe").src = "about:blank";
		try {
			clearInterval(timer_box);
		} catch (e) {
			return;
		}
	}
}


function load_src(isshow, src) {
	if (isshow) {
		var wsize = get_size();
		var width = wsize[0];
		var height = Math.max(wsize[1], wsize[3]);
		var wh = Math.min(wsize[1], wsize[3]);

		var ow = Math.max(400, width - 300); //弹出的宽度
		var oh = Math.max(300, wh - 60); //弹出的高度

		byid("dl_content").style.display = "none";

		byid("dl_layer_div").style.top = byid("dl_layer_div").style.left = "0px";
		byid("dl_layer_div").style.width = width+"px";
		byid("dl_layer_div").style.height = height+"px";
		byid("dl_layer_div").style.display = "block";

		byid("dl_box_div").style.left = (width-ow-16)/2;
		byid("dl_box_div").style.top = 30;
		byid("dl_box_div").style.width = ow + "px";
		byid("dl_box_div").style.height = oh + "px";
		byid("dl_box_div").style.display = "block";


		byid("dl_iframe").style.display = "block";
		byid("dl_set_iframe").src = src;
		byid("dl_set_iframe").style.height = oh - 30 + "px";
		//timer_box = setInterval("reset_iframe_size()", 100);

		set_center(byid("dl_box_div"));

	} else {
		byid("dl_layer_div").style.display = "none";
		byid("dl_box_div").style.display = "none";
		byid("dl_set_iframe").src = "about:blank";
		try {
			clearInterval(timer_box);
		} catch (e) {
			return;
		}
	}
}

global_box_last_height = 0;

function reset_iframe_size(obj) {
	if (!obj) {
		obj = byid("dl_set_iframe");
	}
	var id = obj.id;
	var subWeb = document.frames ? document.frames[id].document : obj.contentDocument;
	try {
		byid("dl_iframe").style.display = "block";
		byid("dl_box_loading").style.display = "none";
	} catch (e) {
		return;
	}
	if(obj && subWeb) {
		var height = subWeb.body.scrollHeight;
		obj.height = height;
		byid("dl_box_title").innerHTML = subWeb.title;
		byid("dl_iframe").style.height = height+"px";
		byid("dl_box_div").style.height = byid("dl_iframe").offsetHeight + byid("dl_box_title_box").offsetHeight + "px";
		//if (global_box_last_height == undefined) global_box_last_height = 0;
		if (global_box_last_height != height) {
			set_center(byid("dl_box_div"));
			global_box_last_height = height;
		}
	}
}


function update_title(obj) {
	if (!obj) {
		obj = byid("dl_set_iframe");
	}
	var id = obj.id;
	var subWeb = document.frames ? document.frames[id].document : obj.contentDocument;
	if(obj != null && subWeb != null) {
		byid("dl_box_title").innerHTML = subWeb.title;
	}
}

function reg_event(obj, event_basename, fn) {
	if (document.all) {
		obj.attachEvent("on"+event_basename, fn);
	} else {
		obj.addEventListener(event_basename, fn, false);
	}
}


function init() {
	// 原始页面标题:
	ori_doc_title = document.title;

	set_body_height();
	reg_event(window, "resize", set_body_height);
	init_top_menu();

	//get_online_list();
	//setInterval("frame_auto_height()", 100);
	//setTimeout("frame_auto_height()", 100);
	//setInterval("get_online_list()", 15000);
	//setInterval("get_online_messages()", 5000);

	get_online();

	// 加载中图标:
	preload("image/loading.gif".split(","));

	if ((guess_mid = location.href.split("#")[1]) && menu_data[guess_mid]) {
		load(guess_mid);
	} else {
		var is_load = false;
		for (var i in menu_mids) {
			tmid = menu_mids[i];
			if (menu_data[tmid] && menu_data[tmid][1]) {
				load(tmid); break;
			}
			for (var nm in menu_stru[tmid]) {
				tiid = menu_stru[tmid][nm];
				if (menu_data[tiid] && menu_data[tiid][1]) {
					load(tiid); is_load = true; break;
				}
			}
			if (is_load) break;
		}
	}
}

// 刷新 sys_frame 内的内容:
function update_content() {
	byid("sys_frame").contentWindow.location.reload();
}
