window.addEventListener('load', function() {
  writeSearchButton();
} );
function writeSearchButton(){
  var prtcl = location.protocol === 'http:' ? 'http:' : 'https:';
  var prefix = prtcl + '//api2.pppark.com';
  var version_dir  = '1.1';
  var _PST_ATAGs_ = document.querySelectorAll("._PPPARK_SEARCH_BUTTON_1");
  for (var i = 0; i<_PST_ATAGs_.length ; i++){
    var _tag = _PST_ATAGs_[i];
    var lat = _tag.getAttribute('data-lat');
    var lng = _tag.getAttribute('data-lng');
    var pref = encodeURIComponent(_tag.getAttribute('data-pref'));
    var city = encodeURIComponent(_tag.getAttribute('data-city'));
    var spot = encodeURIComponent(_tag.getAttribute('data-spot'));
    var parent_ref = encodeURIComponent(location.href);
    var parent_width = _tag.getBoundingClientRect().width;
    var alt_html = _tag.innerHTML;

    //debug
    // var uri = '../../'+this_version+'/inline_frame.html'; console.log('debug mode! on pppark_search_button.js');
    var uri = prefix+'/embed/'+version_dir+'/inline_frame.html';
    _tag.innerHTML = '<iframe src="'+uri+'?parent_width='+parent_width+'&parent_ref='+parent_ref+'&lat='+lat+'&lng='+lng+'&pref='+pref+'&city='+city+'&spot='+spot+'" style="width:'+parent_width+'px;height:28px;overflow:hidden;" frameborder="0" scrolling="no">'+alt_html+'</iframe>';

  }
}
export{
  writeSearchButton
}