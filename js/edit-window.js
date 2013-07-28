var dirty_css = false;
 (function (document, window, $ , dirty ) {
 

var custom_css= {
	css:'',
	init:function(){
		$( "#wp-admin-bar-improved-simpler-css-link" ).on( 'click', popup.open );
		
		custom_css.css = jQuery.trim( jQuery('#simpler-css-style').text() );
	},
	join: function( options ) {
	    var collect = [], part;
	    for (part in options) collect.push( part + '=' + options[part] );
	    
	    return collect.join(',');
	},
	save_css: function( css_data ) {

	    var data = {
			action: 'submit_css',
			css: css_data
		};
		
		return $.post(custom_css_options.ajax_url, data, function( response ) {
			return response;
		});
	}

}

var popup = {
 	win: false,
  	open: function(event){
 		//If popup is already open, give it focus and do nothing else
		if( popup.win && !popup.win.closed ) {	//tested in chrome, firefox and safari...
			popup.win.focus();
			popup.win.editor.gotoLine(0);
			return;
		}
		//open up popup window
			var options = {
					width: parseInt(screen.width * 0.33),
					height: screen.height,
					screenY: 0
				};
				
			options.screenX = screen.width - options.width;
			// console.log(custom_css.join( options ) );
			// Safari won't open popup after XHR calls, so we do it now.
			popup.win = window.open('', 'stylin', custom_css.join( options ) );
			
			popup.win.document.write('<html>')
			popup.win.document.write('<head><style type="text/css" media="screen"> #editor { position: absolute; top: 0; right: 0; bottom: 0;left: 0;} </style></head>');
			popup.win.document.write('<body><div style="padding:10px; margin:-10px;  background:#EEE;"><input type="button" onclick="close_window();return false;" value="Close" /> <input type="button" value="Save" id="save-button" style="float:right;" onclick="save_css();return false;"  /><img src="'+custom_css_options.loader_image+'" id="spinner" alt="loading" style="float:right;margin:0 10px; display:none;" /><div id="msg" style="float:right; font:12px/18px sans-serif; margin-right:10px; color:#AAA ; "></div> </div>');
			
			popup.win.document.write('<div id="editor" style="top:40px;" >'+custom_css.css+'</div>');
			
			popup.win.document.write('<script src="'+custom_css_options.load_ace+'" type="text/javascript" charset="utf-8"></script>');
			popup.win.document.write('<script src="'+custom_css_options.editor+'" type="text/javascript" charset="utf-8"></script>');
			/* popup.win.document.write('<script >console.log(editor, popup)</script>'); */
			
			popup.win.document.write('</body></html>');
			
			popup.win.document.close();
			popup.win.true_save_css = custom_css.save_css; //
			
			
		
			//Prompt the user before closing the parent window if the dirty flag is true since it'll 
			//close the popup too
			window.onbeforeunload = function() {
				
				if (dirty) {
					return 'You have unsaved CSS changes.';
				}
			};
		  
			window.onunload = function() {
				dirty = false;	//ensure there's no second prompt if the popup is still open
				popup.win.close();
				
			};
		
 		}
 	}
 	
 	// lets start everything
	$(custom_css.init);
  
})(document, window, jQuery, dirty_css );
 
 
 