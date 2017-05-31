CKEDITOR.editorConfig = function( config ) {
	config.toolbarGroups = [
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'colors', groups: [ 'colors' ] },
		{ name: 'paragraph', groups: [ 'align', 'list', 'indent', 'blocks', 'bidi', 'paragraph' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others', groups: [ 'others' ] },
		{ name: 'about', groups: [ 'about' ] }
	];
        config.height = 100;
	config.removeButtons = 'Save,NewPage,Preview,Print,Templates,PasteText,PasteFromWord,Copy,Cut,Paste,Scayt,SelectAll,Find,Replace,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Strike,Subscript,Superscript,CopyFormatting,CreateDiv,JustifyBlock,BidiLtr,BidiRtl,Language,Outdent,Indent,Anchor,Flash,SpecialChar,PageBreak,Iframe,ShowBlocks,Maximize,About,Styles,Font';
        config.enterMode = CKEDITOR.ENTER_BR;
        config.extraPlugins = 'font,justify,bbcode';

};

