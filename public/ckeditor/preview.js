// Preview module voor sjabloon-editor
// Vervangt sleutels door dummy data en toont HTML uit CKEditor

document.addEventListener('DOMContentLoaded', function() {
  var previewBtn = document.getElementById('preview-btn');
  var previewContainer = document.getElementById('preview-container');
  var getEditorHtml = function() {
    if (window.CKEDITOR && CKEDITOR.instances['wysiwyg-content']) {
      return CKEDITOR.instances['wysiwyg-content'].getData();
    }
    var textarea = document.getElementById('wysiwyg-content');
    return textarea ? textarea.value : '';
  };
  if (previewBtn && previewContainer) {
    previewBtn.addEventListener('click', function() {
      var html = getEditorHtml();
      var dummyData = window.PREVIEW_KEYS || {};
      Object.keys(dummyData).forEach(function(key) {
        html = html.split(key).join(dummyData[key]);
      });
      previewContainer.innerHTML = html;
      previewContainer.style.display = 'block';
    });
  }
});
