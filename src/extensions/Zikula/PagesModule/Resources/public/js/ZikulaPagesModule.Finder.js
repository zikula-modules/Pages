'use strict';

var currentZikulaPagesModuleEditor = null;
var currentZikulaPagesModuleInput = null;

/**
 * Returns the attributes used for the popup window. 
 * @return {String}
 */
function getZikulaPagesModulePopupAttributes() {
    var pWidth, pHeight;

    pWidth = screen.width * 0.75;
    pHeight = screen.height * 0.66;

    return 'width=' + pWidth + ',height=' + pHeight + ',location=no,menubar=no,toolbar=no,dependent=yes,minimizable=no,modal=yes,alwaysRaised=yes,resizable=yes,scrollbars=yes';
}

/**
 * Open a popup window with the finder triggered by an editor button.
 */
function ZikulaPagesModuleFinderOpenPopup(editor, editorName) {
    var popupUrl;

    // Save editor for access in selector window
    currentZikulaPagesModuleEditor = editor;

    popupUrl = Routing.generate('zikulapagesmodule_external_finder', { objectType: 'page', editor: editorName });

    if (editorName == 'ckeditor') {
        editor.popup(popupUrl, /*width*/ '80%', /*height*/ '70%', getZikulaPagesModulePopupAttributes());
    } else {
        window.open(popupUrl, '_blank', getZikulaPagesModulePopupAttributes());
    }
}


var zikulaPagesModule = {};

zikulaPagesModule.finder = {};

zikulaPagesModule.finder.onLoad = function (baseId, selectedId) {
    if (jQuery('#zikulaPagesModuleSelectorForm').length < 1) {
        return;
    }
    jQuery('select').not("[id$='pasteAs']").change(zikulaPagesModule.finder.onParamChanged);
    
    jQuery('.btn-secondary').click(zikulaPagesModule.finder.handleCancel);

    var selectedItems = jQuery('#zikulapagesmoduleItemContainer a');
    selectedItems.bind('click keypress', function (event) {
        event.preventDefault();
        zikulaPagesModule.finder.selectItem(jQuery(this).data('itemid'));
    });
};

zikulaPagesModule.finder.onParamChanged = function () {
    jQuery('#zikulaPagesModuleSelectorForm').submit();
};

zikulaPagesModule.finder.handleCancel = function (event) {
    var editor;

    event.preventDefault();
    editor = jQuery("[id$='editor']").first().val();
    if ('ckeditor' === editor) {
        zikulaPagesClosePopup();
    } else if ('quill' === editor) {
        zikulaPagesClosePopup();
    } else if ('summernote' === editor) {
        zikulaPagesClosePopup();
    } else if ('tinymce' === editor) {
        zikulaPagesClosePopup();
    } else {
        alert('Close Editor: ' + editor);
    }
};


function zikulaPagesGetPasteSnippet(mode, itemId) {
    var quoteFinder;
    var itemPath;
    var itemUrl;
    var itemTitle;
    var itemDescription;
    var pasteMode;

    quoteFinder = new RegExp('"', 'g');
    itemPath = jQuery('#path' + itemId).val().replace(quoteFinder, '');
    itemUrl = jQuery('#url' + itemId).val().replace(quoteFinder, '');
    itemTitle = jQuery('#title' + itemId).val().replace(quoteFinder, '').trim();
    itemDescription = jQuery('#desc' + itemId).val().replace(quoteFinder, '').trim();
    if (!itemDescription) {
        itemDescription = itemTitle;
    }
    pasteMode = jQuery("[id$='pasteAs']").first().val();

    // item ID
    if ('3' === pasteMode) {
        return '' + itemId;
    }

    // relative link to detail page
    if ('1' === pasteMode) {
        return 'url' === mode ? itemPath : '<a href="' + itemPath + '" title="' + itemDescription + '">' + itemTitle + '</a>';
    }
    // absolute url to detail page
    if ('2' === pasteMode) {
        return 'url' === mode ? itemUrl : '<a href="' + itemUrl + '" title="' + itemDescription + '">' + itemTitle + '</a>';
    }

    return '';
}


// User clicks on "select item" button
zikulaPagesModule.finder.selectItem = function (itemId) {
    var editor, html;

    html = zikulaPagesGetPasteSnippet('html', itemId);
    editor = jQuery("[id$='editor']").first().val();
    if ('ckeditor' === editor) {
        if (null !== window.opener.currentZikulaPagesModuleEditor) {
            window.opener.currentZikulaPagesModuleEditor.insertHtml(html);
        }
    } else if ('quill' === editor) {
        if (null !== window.opener.currentZikulaPagesModuleEditor) {
            window.opener.currentZikulaPagesModuleEditor.clipboard.dangerouslyPasteHTML(window.opener.currentZikulaPagesModuleEditor.getLength(), html);
        }
    } else if ('summernote' === editor) {
        if (null !== window.opener.currentZikulaPagesModuleEditor) {
            if ('3' === jQuery("[id$='pasteAs']").first().val()) {
                window.opener.currentZikulaContentModuleEditor.invoke('insertText', html);
            } else {
                html = jQuery(html).get(0);
                window.opener.currentZikulaPagesModuleEditor.invoke('insertNode', html);
            }
        }
    } else if ('tinymce' === editor) {
        window.opener.currentZikulaPagesModuleEditor.insertContent(html);
    } else {
        alert('Insert into Editor: ' + editor);
    }
    zikulaPagesClosePopup();
};

function zikulaPagesClosePopup() {
    window.opener.focus();
    window.close();
}

jQuery(document).ready(function () {
    zikulaPagesModule.finder.onLoad();
});
