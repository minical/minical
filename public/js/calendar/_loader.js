(function() {

	startload();

	css(css_version['main']);
	css(css_version['common']);
	css(css_version['basic']);

	css(css_version['custom']);

	if (debug && (!window.console || !window.console.log)) {
		jslib('../tests/lib/firebug-lite/firebug-lite-compressed.js');
	}
    
	js(js_version['defaults']);
	js(js_version['main']);
	js(js_version['Calendar']);
	js(js_version['Header']);
	js(js_version['EventManager']);

	js(js_version['RelativeView']);
	js(js_version['BasicView']);
	js(js_version['MonthView']);
    js(js_version['CustomView']);
	js(js_version['BasicEventRenderer']);

	js(js_version['View']);
	js(js_version['DayEventRenderer']);
	js(js_version['SelectionManager']);
	js(js_version['OverlayManager']);
	js(js_version['CoordinateGrid']);
	js(js_version['HoverListener']);
	js(js_version['HorizontalPositionCache']);
	js(js_version['date']);
	js(js_version['util']);

	endload();


	if (debug) {
		window.onload = function() {
			$('body').append(
				"<form style='position:absolute;top:0;right:0;text-align:right;font-size:10px;color:#666'>" +
					"<label for='legacy'>legacy</label> " +
					"<input type='checkbox' id='legacy' name='legacy'" + (legacy ? " checked='checked'" : '') +
						" style='vertical-align:middle' onclick='$(this).parent().submit()' />" +
					"<br />" +
					"<label for='ui'>no jquery ui</label> " +
					"<input type='checkbox' id='ui' name='noui'" + (noui ? " checked='checked'" : '') +
						" style='vertical-align:middle' onclick='$(this).parent().submit()' />" +
				"</form>"
			);
		};
	}


	window.startload = startload;
	window.endload = endload;
	window.css = css;
	window.js = js;
	window.jslib = jslib;


	function startload() {
		debug = false;
		prefix = '';
		tags = [];
		var scripts = document.getElementsByTagName('script');
		for (var i=0, script; script=scripts[i++];) {
			if (!script._checked) {
				script._checked = true;
				var m = (script.getAttribute('src') || '').match(/^(.*)_loader\.([\d]{10})\.js(\?.*)?$/);
				if (m) {
					prefix = m[1];
					debug = (m[2] || '').indexOf('debug') != -1;
					break;
				}
			}
		}
	}


	function endload() {
		document.write(tags.join("\n"));
	}


	function css(file) {
		tags.push("<link rel='stylesheet' type='text/css' href='" + file + "' />");
	}


	function js(file) {
		tags.push("<script type='text/javascript' src='" + file + "'></script>");
	}


	function jslib(file) {
		js(file);
	}


})();
