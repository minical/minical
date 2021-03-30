/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */
CKEDITOR.editorConfig = function( config ) {
    //config.enterMode = CKEDITOR.ENTER_BR;
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
    
    config.toolbarGroups = [
		{ name: 'document', groups: [ 'basicstyles','links','insert','list', 'indent', 'blocks', 'align', 'bidi', 'paragraph', 'mode', 'document', 'doctools','tools' ] },
		//{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		//{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		//{ name: 'forms', groups: [ 'forms' ] },
		'/',
		//{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		//{ name: 'paragraph', groups: [  'align', 'bidi', 'paragraph', 'tools' ] },
		//{ name: 'links', groups: [ 'links' ] },
		//{ name: 'insert', groups: [ 'insert' ] },
		//'/',
		{ name: 'styles', groups: [ ] },
		{ name: 'colors', groups: [ 'styles','colors' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		//{ name: 'others', groups: [ 'others' ] },
		//{ name: 'about', groups: [ 'about' ] }
	];

	config.removeButtons = 'Image,Save,Scayt,Anchor,BidiLtr,BidiRtl,NewPage,Preview,Font,Print,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,Undo,Redo,Find,Replace,SelectAll,Form,Radio,Checkbox,TextField,Textarea,Select,Button,ImageButton,HiddenField,CreateDiv,Language,Flash,PageBreak,Iframe,ShowBlocks,About';
};