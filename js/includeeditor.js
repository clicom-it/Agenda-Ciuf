// configurazione tinymce
function initEditor(ideditor) {
    tinymce.init({
        selector: "textarea#" + ideditor,
        language: 'it',
        schema: "html5",
        toolbar_items_size: 'small',
        menubar: false,
        height: 300,
        plugins: "table, link, image, code, textcolor, paste, hr, autolink",
        table_grid: false, // se tolto finestra creazione tabella visuale
        custom_undo_redo_levels: 20,
        toolbar: "undo redo cut copy paste | bold italic underline | numlist bullist | alignleft aligncenter alignright alignjustify",
        statusbar: false,
        paste_as_text: true,
        relative_urls: false,
        forced_root_block : false
//        file_browser_callback: RoxyFileBrowser
    });
}
// configurazione filemanager
/*
var roxyFileman = './fileman/index.html?integration=tinymce4';
function RoxyFileBrowser(field_name, url, type, win) {
  var cmsURL = roxyFileman;
  if (cmsURL.indexOf("?") < 0) {
    cmsURL = cmsURL + "?type=" + type;
  }
  else {
    cmsURL = cmsURL + "&type=" + type;
  }
  cmsURL += '&input=' + field_name + '&value=' + document.getElementById(field_name).value;
  tinyMCE.activeEditor.windowManager.open({
    file: cmsURL,
    title: 'Roxy File Browser',
    width: 850, // Your dimensions may differ - toy around with them!
    height: 600,
    resizable: "yes",
    plugins: "media",
    inline: "yes", // This parameter only has an effect if you use the inlinepopups plugin!
    close_previous: "no"
	}, {
    window: win,
    input: field_name
	  });
  return false;
}
*/