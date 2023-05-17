/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {


    config.language = 'vi';
    config.embed_provider = '//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}&api_key=8ab5d9bf21c0bfeadf1f5b';
    /* Multi Toolbar */
    config.toolbar_Basic = [
        [ 'Bold', 'Italic', 'BulletedList', 'Format', '-', 'Link','Unlink', 'Image', '-','Source']
    ];
    config.toolbarGroups = [
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'styles', groups: [ 'styles' ] },
        { name: 'colors', groups: [ 'colors' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
        { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
        { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
        { name: 'links', groups: [ 'links' ] },
        { name: 'insert', groups: [ 'insert' ] },
        { name: 'forms', groups: [ 'forms' ] },
        { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
        { name: 'others', groups: [ 'others' ] },
        { name: 'tools', groups: [ 'tools' ] },
        { name: 'about', groups: [ 'about' ] }
    ];

    config.removeButtons = 'Subscript,Superscript,SpecialChar,HorizontalRule,Anchor,Indent,Outdent,Font,ShowBlocks,Form,Radio,Checkbox,TextField,Textarea,Select,Button,ImageButton,HiddenField,Iframe,PageBreak,Save,NewPage,Print,Templates,Styles,Cut,Copy,Paste,CreateDiv,CopyFormatting,Replace,SelectAll';
    config.basicEntities = false;
    config.allowedContent = true;
    /* Allow any class and style in CKEditor. */
   // config.extraAllowedContent = '*(*)';
    config.disallowedContent= "";
    /* Allow only class=”newclass”. */
    //config.extraAllowedContent = '*(newclass)';
    // Allow class="newclass" only for p tag.
    // config.extraAllowedContent = 'p(newclass)';
    // Allow 'id' attribute only.
    // config.extraAllowedContent = '*[id]';
    // Allow style tag (<style type="text/css">...</style>).
    //config.extraAllowedContent = 'style';
    // Allow for all attributes.
    config.extraAllowedContent = '*[id];*(*);*{*};img(*)[*]{*};p(*)[*]{*};div(*)[*]{*};li(*)[*]{*};ul(*)[*]{*};span(*)[*]{*};table(*)[*]{*};td(*)[*]{*}';


};
