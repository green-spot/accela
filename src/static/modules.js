ACCELA.modules.markdown = (object) => object.innerHTML = marked(object.innerHTML);

ACCELA.modules.unescapeMarkdown = (() => {
  const patterns = {
    '&lt;'   : '<',
    '&gt;'   : '>',
    '&amp;'  : '&',
    '&quot;' : '"',
    '&#x27;' : '\'',
    '&#x60;' : '`'
  };

  const unescape = (text) => {
    return text.replace(/&(lt|gt|amp|quot|#x27|#x60);/g, function(match) {
      return patterns[match];
    });
  };

  return (object) => object.innerHTML = marked(unescape(object.innerHTML));
})();
