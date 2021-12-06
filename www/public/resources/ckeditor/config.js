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
        config.format_tags = 'p;h1;h2;h3;h4;h5;h6;pre';
        config.extraPlugins = 'font,justify,bbcode';
        config.removeDialogTabs = 'image:advanced;link:advanced';
        config.language = locale;
};

CKEDITOR.on('dialogDefinition', function( ev ) {
  var dialogName = ev.data.name;
  var dialogDefinition = ev.data.definition;

  if(dialogName === 'table') {
    var infoTab = dialogDefinition.getContents('info');
    var width = infoTab.get('txtWidth');
    width['default'] = "";
    var cellSpacing = infoTab.get('txtCellSpace');
    cellSpacing['default'] = "1";
    var cellPadding = infoTab.get('txtCellPad');
    cellPadding['default'] = "3";
  }
});