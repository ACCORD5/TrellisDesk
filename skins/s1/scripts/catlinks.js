// 
// =========================================
// Trellis Desk v2.0
// Administration Control Panel
// by ACCORD5
// =========================================
// (C) 2008 Aaron Draczynski
// Email: aaron@accord5.com
// Web:   www.accord5.com
// =========================================
// Unauthorized reproduction of design or code is strictly prohibited
// =========================================
// 

//  This is the code to hide the drop-down 'cat_menu' blocks when the user clicks anywhere else on the page.
//  It would be an understatement to say that it was a pain in the behind to get this to work properly.
//  Don't touch any of the code below or you'll mess it all up!  --Aaron
document.onclick=check;
function check(e){
var target = (e && e.target) || (event && event.srcElement);
for (var c = document.getElementsByTagName('div'), i = c.length - 1; i > -1; --i)
if(c[i].className == 'cat_menu')
c[i].style.display = '';
//if(checkParent(target))
//checkParent(target).style.display = 'none';
}

// The code to render the drop-down 'cat_menu' blocks near the user's cursor.
// Original code is copyright 2006, 2007 Bontrager Connection, LLC.
// http://bontragerconnection.com and http://www.willmaster.com
var cX = 0; var cY = 0; var rX = 0; var rY = 0;
function UpdateCursorPosition(e){ cX = e.pageX; cY = e.pageY;}
function UpdateCursorPositionDocAll(e){ cX = event.clientX; cY = event.clientY;}
if(document.all) { document.onmousemove = UpdateCursorPositionDocAll; }
else { document.onmousemove = UpdateCursorPosition; }
function AssignPosition(d) {
if(self.pageYOffset) {
    rX = self.pageXOffset;
    rY = self.pageYOffset;
    }
else if(document.documentElement && document.documentElement.scrollTop) {
    rX = document.documentElement.scrollLeft;
    rY = document.documentElement.scrollTop;
    }
else if(document.body) {
    rX = document.body.scrollLeft;
    rY = document.body.scrollTop;
    }
if(document.all) {
    cX += rX; 
    cY += rY;
    }
d.style.left = (cX+10) + "px";
d.style.top = (cY+10) + "px";
}
function HideContent(d) {
if(d.length < 1) { return; }
document.getElementById(d).style.display = "none";
}
function ShowContent(d) {
if(d.length < 1) { return; }
var dd = document.getElementById(d);
AssignPosition(dd);
dd.style.display = "block";
}
function ReverseContentDisplay(d) {
if(d.length < 1) { return; }
var dd = document.getElementById(d);
AssignPosition(dd);
if(dd.style.display == "none") { dd.style.display = "block"; }
else { dd.style.display = "none"; }
}