/* 
	Uwa base object

 */
self.Uwa = self.Uwa || {};
(function ($) {jQuery(function () {
	// Array.prototype.forEach() shiv //
	// See: https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/Array/forEach
	Array.prototype.forEach||(Array.prototype.forEach=function(a,b){var c,d;if(this==null)throw new TypeError(" this is null or not defined");var e=Object(this),f=e.length>>>0;if(typeof a!="function")throw new TypeError(a+" is not a function");arguments.length>1&&(c=b),d=0;while(d<f){var g;d in e&&(g=e[d],a.call(c,g,d,e)),d++}});
	
	Uwa.UNDEFINED;
	Uwa.root = $(document);
	Uwa.Paths = (function () {
		var jqPluginsDir = '/js/jquery-plugins';
		return  {
			JQUERY_PLUGINS_DIR: jqPluginsDir,
			JQ_PLUGINS: {
				HIGHLIGHTFADE: jqPluginsDir + '/highlightFade.min.js',
				BLOCKUI: jqPluginsDir + '/jquery.blockUI.pak.js',
				FANCYBOX: jqPluginsDir + '/jquery.fancybox.pack.js'
			},
		};
	})();
	
	Uwa.t = function (_str) {
		return $.trim(_str);
	};
	
	Uwa.pint = function (_str) {
		var num = parseInt(_str, 10);
		if (isNaN(num)) { return 0; }
		return num;
	};
	
	
	Uwa.isEmail = function (_email) {
		var emailRegExp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
			e = $.trim(_email);
		if (e.length > 50) { return false }
		return !! emailRegExp.test(e);
	};
	
	Uwa.isNumeric = function (_num) {
		var n = _num;
		// See: http://stackoverflow.com/a/1830844
		return !isNaN(parseFloat(n)) && isFinite(n);
	};
	
	Uwa.hasIssues = function (_$form, _all) {
		var fail = false,
			t = $.trim;
		_all = _all || false;
		var $fields = _$form.find('input, select, textarea').filter('.required, .number, .required_if_has_value');
		if (_all == false) {
			$fields = $fields.filter(':visible')
		}
		$fields.removeClass('issue').each(function () {
			var $field = jQuery(this),
				v = t($field.val());								
			if (v == '' || parseInt(v, 10) == 0) {
				if (! $field.hasClass('required_if_has_value')) {
					fail = true;
					$field.addClass('issue');
				}					
			}
			else {
				if ($field.hasClass('email')) {
					if (! Uwa.isEmail(v)) {
						fail = true;
						$field.addClass('issue');
					}
				}
				else if ($field.hasClass('number')) {
					if (! Uwa.isNumeric(v)) {
						fail = true;
						$field.addClass('issue');
					}
				}
			}	
		});
		return fail;
	};
	
	
	Uwa.log = function (_log, _method) {
		// See: http://www.paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
		_method = _method || 'log';
		if (self.console) { 
			console[_method](_log);
		}
	};
	
	Uwa.tpl = function (s,d){
		// See: http://mir.aculo.us/2011/03/09/little-helpers-a-tweet-sized-javascript-templating-engine/	
		for(var p in d) {
			s=s.replace(new RegExp('{'+p+'}','g'), d[p]);
		}	   
		return s;
	};
	
	Uwa.Image = {};
	Uwa.Image.isValid = function (_filename) {
		var imgExt = 'png,jpg,jpeg,gif'.split(','),
			ext = Eb.t(_filename).toLowerCase().split('.').pop();
		if ($.inArray(ext, imgExt) === -1) { return false; }	
		return true;
	};
	
	Uwa.sc = (function () {
		// Selector Cacher //
		var elems = {};
		return function (_selector) {
			if (! elems[_selector]) {
				elems[_selector] = $(_selector);
			}
			return elems[_selector];
		};
	})();
	
	Uwa.getScriptOnce = (function () {
		var loaded = [];
		return function (_js, _callback) {
			_js = Uwa.t(_js);
			_callback = _callback || function () {};
			if ($.inArray(_js, loaded) === -1) {
				$.getScript(_js, _callback);
				loaded.push(_js);
				return;
			}
			_callback();
		};
	})();
	
	Events = {	
		focusInRemoveIssueClass: function () {
			$(this).removeClass('issue');
		}	
	};	
	
	// Call events here //
	$(document).on('focusin', '.issue', Events.focusInRemoveIssueClass);
	
	(function () {
		// Global runs //
		// Place code that immediately runs on page dom ready here. //
		
		// Default block ui style //
		if (typeof $.fn.block !== 'undefined') {
			$.blockUI.defaults.overlayCSS = { 
				backgroundColor: '#fff', 
				opacity: 0.6, 
				cursor: 'wait' 
			};		
		}
	})();
});})(jQuery);
