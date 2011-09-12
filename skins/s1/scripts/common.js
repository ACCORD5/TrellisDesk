function goToUrl(url) {
    location.href = url;
}

$.fn.clearOnFocus = function() {
    return this.focus(function() {
        if ( this.value == this.defaultValue ) {
            this.value = "";
        }
    }).blur(function() {
        if ( !this.value.length ) {
            this.value = this.defaultValue;
        }
    });
};

$(function(){
    $("#username").clearOnFocus();
    $("#password").clearOnFocus();
    $("#search").clearOnFocus();
 });