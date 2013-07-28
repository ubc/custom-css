
jQuery(document).ready(function ($) {	
	var editor = {};
	// initialize ACE Editor for the WP post content textarea#content
	$('#editor').AceEditor({
		// overwrites the default
		setEditorContent: function () {
			var value = this.$elem.val();
			editor = this.editor;
			this.editor.getSession().setValue( value );	
		},
		onInit: function () {
			this.load();
		},
		onLoaded: function () {
			
		},
		onDestroy: function (e) {
				// console.log(this.editor)		
		}
	});
	$('#editor').submit(function(){
		
		var js = editor.getValue();
		
		$('#editor').val( js );
		
	
	
	});
	

});

(function ($) {
	
	var AceSettings = AceSettings || {};	
	
	// constructor function
	var AceEditor = function (config) {
		// unpacks all of config and adds it to 'this'
		$.extend(this, config);
		
		// element = the textarea with the content to bring into the ace editor
		this.$elem = this.element;
		this.element = this.$elem.attr('id');
		
		// set the container as the first parent if not provided in options
		this.$container = this.container ? $(this.container) : this.$elem.parent();	
		this.contWd = this.$container.width();
		this.loaded = false;
		// if tinymce shows up, assume we have visual mode enabled on this page
		this.tinymce = !!window.tinymce;
		
		if (this.onInit) this.onInit.call(this);		
	};

	AceEditor.prototype = {
		
		load: function () {
			if (this.loaded) return false;
			var self = this;
			// hide the textarea
			this.$elem.hide();
			// insert the editor div
			this.insertEditor();	
			// init the ace editor
			this.editor = ace.edit(this.aceId);
			this.$editor = $('#' + this.aceId);
			// set some ace properties
			this.setEditorProps();
			// set editor content - either content of textarea or tinymce
			this.setEditorContent();	
			// make the container div resizable in y-direction
			this.containerResizable();
			// update the textarea when the content in the ace div changes
			this.editor.on('change', function () {self.synchronize.apply(self);});		
			// trigger the initial resize event
			this.editor.resize(true);
			// execute callback if it exists
			this.loaded = true;
			if (this.onLoaded) this.onLoaded.call(this);
		},
		
		insertEditor: function () {
			$('<div id="' + this.aceId + '"></div>')
				.css({left: 0, top: 0, bottom: 0, right: 0, zIndex: 1 })
				.insertAfter(this.$elem);
		},
		
		setEditorProps: function () {
			this.editor.setTheme("ace/theme/clouds");
			this.editor.getSession().setMode('ace/mode/css');
			this.editor.getSession().setUseWrapMode(true);
			this.editor.getSession().setWrapLimitRange();
		},
		
		setEditorContent: function () {
			this.editor.getSession().setValue(this.$elem.val()); // seems like html, val, or text OK		
		},
		
		containerResizable: function () {
			var self = this;
			this.$container
				.resizable({handles: 's'})
				.css({position: 'relative', height: this.defaultHt, minHeight: '400px'})
				.on('resize.aceEditorResize', function() {
					self.editor.resize(true);
				});	
		},
		
		synchronize: function () {
			var val = this.editor.getValue();
			this.$elem.val(val); // text, val, html ??
			if (this.tinymce && tinyMCE.get(this.element)) tinyMCE.get(this.element).setContent(val);
		},
		
		destroy: function () {
			if (!this.loaded) return false;
			this.$editor.remove();
			this.editor.destroy();
			this.$container.resizable('destroy').off('resize.aceEditorResize').css({height: ''});
			this.$elem.show();
			this.loaded = false;
			if (this.onDestroy) this.onDestroy.apply(this, arguments);
		}
		
	};
	
	// jquery plugin for ace editor
	// gives us an entry point for method calls if needed in the future
	$.fn.AceEditor = function (option, value) {
		var option = option || null;
		var data = $(this).data('AceEditor');
		// if data exists (has been instantiated) and calling a public method
		if (data && typeof option == 'string' && data[option]) {
			data[option](value || null);
		// if no data, then instantiate the plugin
		} else if (!data) {
			return this.each(function () {
				var config = $.extend({
					element: $(this),
					aceId: 'ace-editor',
					theme: 'textmate',
					defaultHt: '600px',
					container: false
				}, option);		
				$(this).data('AceEditor', new AceEditor(config));
			});
		// else, throw jquery error
		} else {
			$.error( 'Method "' +  option + '" does not exist on AceEditor!');
		}
	};
	
	
})(jQuery);


